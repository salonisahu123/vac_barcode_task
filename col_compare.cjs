const fs = require('fs');
const t1 = fs.readFileSync('actual_local_schema.sql', 'utf8');
const t2 = fs.readFileSync('vac_task.sql', 'utf8');

const getTables = (text) => {
    return [...text.matchAll(/CREATE TABLE `([^`]+)` \([\s\S]*?\)(?= ENGINE|;)/g)]
        .map(m => {
            const name = m[1];
            const def = m[0];
            const columns = [...def.matchAll(/^\s*`([^`]+)`/gm)].map(c => c[1]);
            return { name, columns };
        });
};

const db1 = getTables(t1);
const db2 = getTables(t2);

const db1Map = new Map(db1.map(t => [t.name, t.columns]));
const db2Map = new Map(db2.map(t => [t.name, t.columns]));

let hasDiff = false;
for (const [k, v] of db1Map.entries()) {
    if (db2Map.has(k)) {
        const prodCols = db2Map.get(k);
        const localCols = v;
        const missingInProd = localCols.filter(c => !prodCols.includes(c));
        const missingInLocal = prodCols.filter(c => !localCols.includes(c));
        
        if (missingInProd.length > 0 || missingInLocal.length > 0) {
            console.log(`Table: ${k}`);
            if (missingInProd.length > 0) console.log(`  Columns missing in Production: ${missingInProd.join(', ')}`);
            if (missingInLocal.length > 0) console.log(`  Columns missing in Local: ${missingInLocal.join(', ')}`);
            hasDiff = true;
        }
    }
}
if (!hasDiff) console.log('No differences in table columns found between local and production.');
