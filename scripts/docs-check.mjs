// Documentation gate of the docs-check job: links and anchors, identifiers, and the consistency of the Task plan.
// Usage: node scripts/docs-check.mjs [root]
import { spawnSync } from 'node:child_process';
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative, resolve } from 'node:path';

const root = resolve(process.argv[2] ?? '.');
const problems = [];

const links = spawnSync(
    process.execPath,
    [join(import.meta.dirname, 'docs/check-links.mjs'), root],
    { encoding: 'utf8' },
);
process.stdout.write(links.stdout);
process.stderr.write(links.stderr);

if (links.status !== 0) {
    problems.push('links: see the problems above');
}

function read(path) {
    return readFileSync(join(root, path), 'utf8').replace(/\r\n/g, '\n');
}

function markdownFiles(folder) {
    return readdirSync(folder).flatMap((name) => {
        const path = join(folder, name);

        if (statSync(path).isDirectory()) {
            return markdownFiles(path);
        }

        return name.endsWith('.md') ? [path] : [];
    });
}

// --- Task plan: the status table is the truth; counters, checklist, contracts and handoff must agree with it.
const plan = read('docs/11-tasks/TASK_PLAN.md');
const rows = [...plan.matchAll(/^\| \[(TASK-\d{3})\]\(\1\.md\) \| .*? \| ([A-Z_]+) \|/gm)].map(
    ([, id, status]) => ({ id, status }),
);
const count = (status) => rows.filter((row) => row.status === status).length;
const statuses = ['DONE', 'READY', 'IN_PROGRESS', 'BLOCKED', 'VERIFYING', 'TODO', 'SUPERSEDED'];

function counter(name) {
    const match = plan.match(new RegExp(`^${name}: (\\d+)$`, 'm'));

    return match ? Number(match[1]) : null;
}

const current = counter('Current Total Tasks');

if (current !== rows.length) {
    problems.push(`TASK_PLAN: Current Total Tasks is ${current}, the table has ${rows.length} rows`);
}

for (const status of statuses) {
    if (counter(status) !== count(status)) {
        problems.push(`TASK_PLAN: ${status} is ${counter(status)}, the table has ${count(status)}`);
    }
}

const done = count('DONE');
const progress = plan.match(/^Progress: (\d+) \/ (\d+)$/m);

if (!progress || Number(progress[1]) !== done || Number(progress[2]) !== rows.length) {
    problems.push(`TASK_PLAN: Progress does not match ${done} / ${rows.length}`);
}

const percentage = plan.match(/^Percentage: (\d+)%$/m);

if (!percentage || Number(percentage[1]) !== Math.round((done / rows.length) * 100)) {
    problems.push('TASK_PLAN: Percentage does not match the progress');
}

for (const { id, status } of rows) {
    const file = `docs/11-tasks/${id}.md`;

    if (!existsSync(join(root, file))) {
        problems.push(`TASK_PLAN: ${file} does not exist`);
        continue;
    }

    const contract = read(file).match(/^STATUS: ([A-Z_]+)$/m)?.[1];

    // READY and IN_PROGRESS live in the plan until reconciliation, so only the final states must match.
    if (['DONE', 'SUPERSEDED'].includes(status) !== ['DONE', 'SUPERSEDED'].includes(contract)) {
        problems.push(`TASK_PLAN: ${id} is ${status}, its contract says ${contract}`);
    }

    const checked = new RegExp(`^- \\[x\\] \\[${id}\\]`, 'm').test(plan);

    if (checked !== (status === 'DONE')) {
        problems.push(`TASK_PLAN: the checklist entry of ${id} does not match ${status}`);
    }
}

const handoff = read('docs/handoff/CURRENT_HANDOFF.md').match(/\| Task plan \| (.*?) \|$/m)?.[1] ?? '';

for (const status of statuses) {
    const value = handoff.match(new RegExp(`${status} (\\d+)`))?.[1];

    if (Number(value) !== count(status)) {
        problems.push(`CURRENT_HANDOFF: ${status} is ${value}, TASK_PLAN has ${count(status)}`);
    }
}

// --- Identifiers: a reference must point at something that exists.
const known = {
    TASK: new Set(rows.map((row) => row.id)),
    GAP: new Set(read('docs/00-governance/GAP_REGISTER.md').match(/\bGAP-\d{3}\b/g)),
    OD: new Set(read('docs/00-governance/DECISION_LOG.md').match(/\bOD-\d{2}\b/g)),
};
const docs = markdownFiles(join(root, 'docs'));
let references = 0;

for (const file of docs) {
    const where = relative(root, file).replaceAll('\\', '/');

    // Historical evidence and the imported framework use their own numbering.
    if (where.startsWith('docs/00-governance/sources/')) {
        continue;
    }

    const text = readFileSync(file, 'utf8');

    for (const [kind, pattern] of Object.entries({
        TASK: /\bTASK-\d{3}\b/g,
        GAP: /\bGAP-\d{3}\b/g,
        OD: /\bOD-\d{2}\b/g,
    })) {
        for (const id of new Set(text.match(pattern))) {
            references += 1;

            if (!known[kind].has(id)) {
                problems.push(`${where}: unknown identifier ${id}`);
            }
        }
    }
}

for (const problem of problems) {
    console.error(problem);
}

console.log(
    `docs-check: ${rows.length} Tasks, ${references} identifier references, ${problems.length} problems`,
);
process.exit(problems.length === 0 ? 0 : 1);
