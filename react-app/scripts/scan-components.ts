import fs from 'fs';
import path from 'path';

interface ComponentInfo {
  name: string;
  path: string;
  props: string[];
  usedIn: string[];
}

function scanComponents(dir: string): ComponentInfo[] {
  const components: ComponentInfo[] = [];
  const files = fs.readdirSync(dir);

  for (const file of files) {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);

    if (stat.isDirectory()) {
      components.push(...scanComponents(filePath));
    } else if (file.endsWith('.tsx') || file.endsWith('.jsx')) {
      const content = fs.readFileSync(filePath, 'utf-8');
      const componentMatch = content.match(/(?:export\s+)?(?:function|const)\s+(\w+)/);

      if (componentMatch) {
        const name = componentMatch[1];
        const props = extractProps(content);

        components.push({
          name,
          path: filePath,
          props,
          usedIn: []
        });
      }
    }
  }

  return components;
}

function extractProps(content: string): string[] {
  const propsMatch = content.match(/interface\s+\w+Props\s*\{([^}]+)\}/);
  if (!propsMatch) return [];

  const propsBlock = propsMatch[1];
  const props = propsBlock
    .split('\n')
    .map(line => line.trim())
    .filter(line => line && !line.startsWith('//'))
    .map(line => line.split(':')[0].replace('?', '').trim());

  return props;
}

// Run scanner
const components = scanComponents('./src');
fs.writeFileSync(
  './component-registry.json',
  JSON.stringify(components, null, 2)
);
console.log(`Scanned ${components.length} components`);
