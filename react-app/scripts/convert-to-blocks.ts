import mappings from '../component-mappings.json';

interface Block {
  id: string;
  type: string;
  data: Record<string, any>;
}

type MappingEntry = {
  blockType: string;
  propMapping: Record<string, string>;
  transformation?: string;
  dataSource?: string;
};

export function convertComponentToBlock(
  componentName: string,
  props: Record<string, any>
): Block | null {
  const mapping = (mappings.mappings as Record<string, MappingEntry>)[componentName];
  if (!mapping) return null;

  const blockData: Record<string, any> = {};

  for (const [propName, blockField] of Object.entries(mapping.propMapping)) {
    const value = props[propName];

    if (mapping.transformation === 'first-image-only' && propName === 'images') {
      blockData[blockField] = Array.isArray(value) ? value[0] : value;
    } else {
      blockData[blockField] = value;
    }
  }

  return {
    id: generateId(),
    type: mapping.blockType,
    data: blockData
  };
}

function generateId(): string {
  return `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}
