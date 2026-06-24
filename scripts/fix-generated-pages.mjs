#!/usr/bin/env node
import fs from 'fs';
import path from 'path';

const root = path.resolve(import.meta.dirname, '..', 'resources/js/pages');

function walk(dir, files = []) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);

        if (entry.isDirectory()) {
walk(full, files);
} else if (entry.name === 'create.tsx' || entry.name === 'edit.tsx' || entry.name === 'show.tsx') {
files.push(full);
}
    }

    return files;
}

for (const file of walk(root)) {
    let c = fs.readFileSync(file, 'utf8');
    const isEdit = file.endsWith('edit.tsx');
    const isCreate = file.endsWith('create.tsx');
    const isShow = file.endsWith('show.tsx');

    if (isCreate || isEdit) {
        const routeMatch = c.match(/@\/routes\/[a-z0-9-/]+/);

        if (!routeMatch) {
continue;
}

        const route = routeMatch[0];
        const entityMatch = c.match(/export default function Page\(\{ ([^}]+) \}/);
        const entity = entityMatch?.[1]?.split(',')[0]?.trim().split(':')[0]?.trim();

        if (isCreate) {
            c = c.replace(/import \{ (store|update) \} from '[^']+';\n/g, '');

            if (!c.includes("import { store }")) {
                c = c.replace("import PageHeader", `import { store } from '${route}';\nimport PageHeader`);
            }

            c = c.replace(
                /const isEdit = Boolean\([^)]*\);\s*\n\s*const route = [^;]+;/,
                'const route = store.form();',
            );
            c = c.replace(/export default function Page\(\{ plans, name \}/, 'export default function Page({ plans }');
        }

        if (isEdit && entity) {
            c = c.replace(/import \{ (store|update) \} from '[^']+';\n/g, '');

            if (!c.includes("import { update }")) {
                c = c.replace("import PageHeader", `import { update } from '${route}';\nimport PageHeader`);
            }

            c = c.replace(
                /const isEdit = Boolean\([^)]*\);\s*\n\s*const route = [^;]+;/,
                `const route = update.form(${entity});`,
            );
        }
    }

    if (isShow) {
        const entity = c.match(/export default function Page\(\{ (\w+) \}/)?.[1];

        if (entity) {
            c = c.replace(
                /editHref="([^"]*\$\{[^}]+\}[^"]*)"/g,
                (m, url) => `editHref={\`${url}\`}`,
            );
            c = c.replace(
                /deleteHref="([^"]*\$\{[^}]+\}[^"]*)"/g,
                (m, url) => `deleteHref={\`${url}\`}`,
            );
        }
    }

    fs.writeFileSync(file, c);
}

console.log('Fixed form and show pages');
