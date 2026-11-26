const fs = require('fs');

const t1 = fs.readFileSync('actual_local_schema.sql', 'utf8');
const t2 = fs.readFileSync('vac_task.sql', 'utf8');

const getTables = (text) => {
    return [...text.matchAll(/CREATE TABLE `([^`]+)` \([\s\S]*?\)(?= ENGINE|;)/g)]
        .map(m => {
            const name = m[1];
            const def = m[0];
            const cols = {};
            for (const match of def.matchAll(/^\s*`([^`]+)`\s*(.*?)(?=,$|\s*$)/gm)) {
                // remove COLLATE and AUTO_INCREMENT to reduce noise
                let cleanDef = match[2].replace(/COLLATE \S+/g, '').replace(/AUTO_INCREMENT/g, '').replace(/DEFAULT current_timestamp\(\) ON UPDATE current_timestamp\(\)/gi, 'DEFAULT CURRENT_TIMESTAMP').replace(/DEFAULT current_timestamp\(\)/gi, 'DEFAULT CURRENT_TIMESTAMP').replace(/int\(\d+\)/g, 'int').replace(/bigint\(\d+\)/g, 'bigint').trim();
                cols[match[1]] = cleanDef;
            }
            return { name, cols };
        });
};

const db1 = getTables(t1);
const db2 = getTables(t2);

const db1Map = new Map(db1.map(t => [t.name, t.cols]));
const db2Map = new Map(db2.map(t => [t.name, t.cols]));

let out = "";
for (const [k, localCols] of db1Map.entries()) {
    if (db2Map.has(k)) {
        const prodCols = db2Map.get(k);
        for (const colName of Object.keys(localCols)) {
            if (!prodCols[colName]) {
                out += `ALTER TABLE \`${k}\` ADD \`${colName}\` ${localCols[colName]};\n`;
            } else if (prodCols[colName] !== localCols[colName]) {
                // out += `-- Diff in ${k}.${colName}: Local [${localCols[colName]}] vs Prod [${prodCols[colName]}]\n`;
                // Try to detect actual important diffs, ignoring minor formatting
                let l = localCols[colName].replace(/\s+/g, ' ').toLowerCase();
                let p = prodCols[colName].replace(/\s+/g, ' ').toLowerCase();
                if (l !== p) {
                     out += `ALTER TABLE \`${k}\` MODIFY \`${colName}\` ${localCols[colName]};\n`;
                }
            }
        }
    } else {
        // Table missing in prod
        out += `-- Table ${k} is missing in production\n`;
    }
}

fs.writeFileSync('alter_queries.sql', out);
console.log('Done writing alter queries to alter_queries.sql');
