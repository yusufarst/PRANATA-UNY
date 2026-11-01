// Checks relative links and heading anchors in the Markdown files of this repository.
// Usage: node scripts/docs/check-links.mjs [root]
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { dirname, join, relative, resolve } from 'node:path';

const root = resolve(process.argv[2] ?? '.');
const skippedFolders = new Set([
    '.git',
    '.pranata',
    'node_modules',
    'reference-inputs',
    'storage',
    'vendor',
]);

function markdownFiles(folder) {
    return readdirSync(folder).flatMap((name) => {
        const path = join(folder, name);

        if (statSync(path).isDirectory()) {
            return skippedFolders.has(name) ? [] : markdownFiles(path);
        }

        return name.endsWith('.md') ? [path] : [];
    });
}

// Outside fenced code and inline code, so examples are not read as links.
function prose(text) {
    return text
        .replace(/^(```|~~~)[\s\S]*?^\1.*$/gm, '')
        .replace(/`[^`\n]*`/g, '');
}

// GitHub's heading slug: lowercase, drop punctuation, each space becomes a hyphen.
function slug(heading) {
    return heading
        .trim()
        .replace(/!?\[([^\]]*)\]\([^)]*\)/g, '$1')
        .replace(/[*_`]/g, '')
        .toLowerCase()
        .replace(/[^\p{L}\p{N}\s-]/gu, '')
        .replace(/\s/g, '-');
}

const anchorCache = new Map();

function anchorsOf(file) {
    if (!anchorCache.has(file)) {
        const text = readFileSync(file, 'utf8');
        const seen = new Map();
        const anchors = new Set();

        for (const match of text
            .replace(/^(```|~~~)[\s\S]*?^\1.*$/gm, '')
            .matchAll(/^#{1,6}\s+(.+?)\s*#*\s*$/gm)) {
            const base = slug(match[1]);
            const count = seen.get(base) ?? 0;
            anchors.add(count === 0 ? base : `${base}-${count}`);
            seen.set(base, count + 1);
        }

        for (const match of text.matchAll(/<a\s+(?:name|id)="([^"]+)"/g)) {
            anchors.add(match[1]);
        }

        anchorCache.set(file, anchors);
    }

    return anchorCache.get(file);
}

const problems = [];
let linkCount = 0;

for (const file of markdownFiles(root)) {
    const text = prose(readFileSync(file, 'utf8'));

    for (const match of text.matchAll(/\]\(([^)\s]+)(?:\s+"[^"]*")?\)/g)) {
        const target = match[1];

        if (/^[a-z][a-z0-9+.-]*:/i.test(target)) {
            continue;
        }

        linkCount += 1;

        const [path, anchor] = target.split('#');
        const destination = path ? resolve(dirname(file), decodeURI(path)) : file;
        const where = relative(root, file).replaceAll('\\', '/');

        if (!existsSync(destination)) {
            problems.push(`${where}: missing file ${target}`);
        } else if (
            anchor &&
            destination.endsWith('.md') &&
            !anchorsOf(destination).has(decodeURI(anchor).toLowerCase())
        ) {
            problems.push(`${where}: missing anchor ${target}`);
        }
    }
}

for (const problem of problems) {
    console.error(problem);
}

console.log(`docs: ${linkCount} relative links checked, ${problems.length} problems`);
process.exit(problems.length === 0 ? 0 : 1);
