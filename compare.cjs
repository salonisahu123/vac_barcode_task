const fs = require('fs');
const t1 = fs.readFileSync('actual_local_schema.sql', 'utf8');
const t2 = fs.readFileSync('vac_task.sql', 'utf8');

const getTables = (text) => {
    return [...text.matchAll(/CREATE TABLE `([^`]+)` \([\s\S]*?\)(?= ENGINE|;)/g)]
        .map(m => ({ name: m[1], def: m[0] }));
};

const getAlters = (text) => {
    return [...text.matchAll(/ALTER TABLE `([^`]+)`([\s\S]*?);/g)]
        .map(m => ({ name: m[1], def: m[0] }));
};

const db1 = getTables(t1);
const db2 = getTables(t2);

const db1Map = new Map(db1.map(t => [t.name, t.def]));
const db2Map = new Map(db2.map(t => [t.name, t.def]));

console.log('Tables in current db (local) but not in production:', [...db1Map.keys()].filter(k => !db2Map.has(k)));
console.log('Tables in production but not in current db (local):', [...db2Map.keys()].filter(k => !db1Map.has(k)));

console.log('\n--- Diff in Tables ---');
for (const [k, v] of db1Map.entries()) {
    if (db2Map.has(k) && db2Map.get(k) !== v) {
        console.log(`Diff in table: ${k}`);
        console.log('--- Local ---');
        console.log(v);
        console.log('--- Production ---');
        console.log(db2Map.get(k));
        console.log('------------------\n');
    }
}
