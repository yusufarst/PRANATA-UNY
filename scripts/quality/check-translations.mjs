// Compares the id and en translation catalogs (LOCALIZATION): a key in one language must exist in the other.
// Interface text is JSON under resources/js/locales; server text is PHP under lang, read with the php binary.
// Usage: node scripts/quality/check-translations.mjs [root], where root holds the catalog folders.
import { execFileSync } from 'node:child_process';
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const root = process.argv[2] ?? '.';
const catalogRoots = ['resources/js/locales', 'lang'];

function catalogFiles(folder) {
    if (!existsSync(folder)) {
        return [];
    }

    return readdirSync(folder).flatMap((name) => {
        const path = join(folder, name);

        if (statSync(path).isDirectory()) {
            return catalogFiles(path).map((child) => join(name, child));
        }

        return /\.(json|php)$/.test(name) ? [name] : [];
    });
}

function readCatalog(path) {
    if (path.endsWith('.json')) {
        return JSON.parse(readFileSync(path, 'utf8'));
    }

    return JSON.parse(
        execFileSync('php', ['-r', 'echo json_encode(require $argv[1]);', path], {
            encoding: 'utf8',
        }),
    );
}

function flatten(value, prefix = '') {
    return Object.entries(value).flatMap(([key, child]) => {
        const path = prefix ? `${prefix}.${key}` : key;

        return child !== null && typeof child === 'object'
            ? flatten(child, path)
            : [path];
    });
}

const problems = [];
let keyCount = 0;

for (const catalogRoot of catalogRoots) {
    const id = join(root, catalogRoot, 'id');
    const en = join(root, catalogRoot, 'en');
    const files = new Set([...catalogFiles(id), ...catalogFiles(en)]);

    for (const file of files) {
        const read = (folder) => {
            const path = join(folder, file);

            return existsSync(path) ? new Set(flatten(readCatalog(path))) : new Set();
        };
        const idKeys = read(id);
        const enKeys = read(en);
        keyCount += idKeys.size;

        for (const key of idKeys) {
            if (!enKeys.has(key)) {
                problems.push(`${relative(root, join(en, file))}: missing ${key}`);
            }
        }

        for (const key of enKeys) {
            if (!idKeys.has(key)) {
                problems.push(`${relative(root, join(id, file))}: missing ${key}`);
            }
        }
    }
}

for (const problem of problems) {
    console.error(problem);
}

console.log(`translations: ${keyCount} keys checked, ${problems.length} problems`);
process.exit(problems.length === 0 ? 0 : 1);
