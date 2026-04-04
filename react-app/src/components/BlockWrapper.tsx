import React, { useEffect, useRef } from 'react';

interface BlockWrapperProps {
  blockId: string;
  blockType: string;
  children: React.ReactNode;
}

export function BlockWrapper({ blockId, blockType, children }: BlockWrapperProps) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    // Replace server-rendered HTML with React component
    if (ref.current) {
      const serverBlock = document.querySelector(`[data-block-id="${blockId}"]`);
      if (serverBlock && serverBlock.parentNode) {
        serverBlock.parentNode.replaceChild(ref.current, serverBlock);
      }
    }
  }, [blockId]);

  return (
    <div ref={ref} data-block-id={blockId} data-block-type={blockType}>
      {children}
    </div>
  );
}
