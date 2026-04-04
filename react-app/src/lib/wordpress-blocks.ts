import { useQuery } from '@tanstack/react-query';

declare global {
  interface Window {
    opusData?: {
      apiUrl: string;
      pageId: number;
      locale: string;
      themeUrl: string;
    };
  }
}

const getApiUrl = () =>
  window.opusData?.apiUrl || '/wp-json/lovable/v1';

export interface BlockData {
  id: string;
  type: string;
  data: Record<string, any>;
}

export interface PageBlocks {
  blocks: BlockData[];
  layout: Array<{ id: string; order: number }>;
}

export async function fetchPageBlocks(pageId: number): Promise<PageBlocks> {
  const response = await fetch(`${getApiUrl()}/page/${pageId}`);
  if (!response.ok) {
    throw new Error('Failed to fetch blocks');
  }
  return response.json();
}

export function usePageBlocks(pageId: number) {
  return useQuery({
    queryKey: ['page-blocks', pageId],
    queryFn: () => fetchPageBlocks(pageId),
    staleTime: 1000 * 60 * 5, // 5 minutes
    enabled: !!window.opusData, // Only fetch when running inside WordPress
  });
}

export function useBlockById(pageId: number, blockId: string) {
  const { data } = usePageBlocks(pageId);
  return data?.blocks.find(block => block.id === blockId);
}
