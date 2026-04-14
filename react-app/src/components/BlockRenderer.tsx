import { BlockData } from '@/lib/wordpress-blocks';
import { BlockWrapper } from './BlockWrapper';
import HeroSlideshow from './HeroSlideshow';

interface BlockRendererProps {
  block: BlockData;
}

export function BlockRenderer({ block }: BlockRendererProps) {
  switch (block.type) {
    case 'hero':
      return (
        <BlockWrapper blockId={block.id} blockType="hero">
          <HeroSlideshow className="h-screen" />
        </BlockWrapper>
      );
    case 'gallery_grid':
      return (
        <BlockWrapper blockId={block.id} blockType="gallery_grid">
          <div className="max-w-[1400px] mx-auto px-6 md:px-12 py-24">
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
              {(block.data.items || []).map((item: any, i: number) => (
                <div key={i} className="group">
                  <div className="relative overflow-hidden aspect-[3/4]">
                    <img src={item.image} alt={item.title || ''} className="w-full h-full object-cover" loading="lazy" />
                  </div>
                  <div className="mt-4">
                    <h3 className="font-display text-lg text-foreground">{item.title}</h3>
                    {item.artist && <p className="font-accent text-sm text-muted-foreground mt-1 italic">{item.artist}</p>}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </BlockWrapper>
      );
    case 'artist_card':
      return (
        <BlockWrapper blockId={block.id} blockType="artist_card">
          <div className="group block">
            <div className="relative overflow-hidden aspect-square">
              <img src={block.data.image} alt={block.data.name} className="w-full h-full object-cover" loading="lazy" />
              <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent" />
              <div className="absolute bottom-0 left-0 right-0 p-5">
                <h3 className="font-display text-xl text-white">{block.data.name}</h3>
                {block.data.bio && <p className="font-body text-xs text-white/70 mt-1">{block.data.bio}</p>}
              </div>
            </div>
          </div>
        </BlockWrapper>
      );
    case 'cta':
      return (
        <BlockWrapper blockId={block.id} blockType="cta">
          <section className="relative py-36 md:py-48 bg-background/75">
            <div className="max-w-[1400px] mx-auto px-6 md:px-12">
              <h2 className="font-display text-4xl md:text-6xl text-foreground">{block.data.title}</h2>
              {block.data.description && <p className="font-body text-base text-muted-foreground mt-8 max-w-xl">{block.data.description}</p>}
              {block.data.buttonText && (
                <a href={block.data.buttonLink || '#'} className="inline-flex items-center gap-4 px-10 py-5 mt-12 border border-primary text-primary hover:bg-primary hover:text-background transition-colors">
                  <span className="font-body text-sm tracking-[0.2em] uppercase">{block.data.buttonText}</span>
                </a>
              )}
            </div>
          </section>
        </BlockWrapper>
      );
    case 'text_block':
      return (
        <BlockWrapper blockId={block.id} blockType="text_block">
          <div className={`max-w-[1400px] mx-auto px-6 md:px-12 py-24 text-${block.data.alignment || 'left'}`}>
            <div className="font-body text-base text-foreground leading-relaxed" dangerouslySetInnerHTML={{ __html: block.data.content }} />
          </div>
        </BlockWrapper>
      );
    case 'feature_grid':
      return (
        <BlockWrapper blockId={block.id} blockType="feature_grid">
          <div className="max-w-[1400px] mx-auto px-6 md:px-12 py-24">
            <div className={`grid sm:grid-cols-2 lg:grid-cols-${block.data.columns || 3} gap-8`}>
              {(block.data.features || []).map((feature: any, i: number) => (
                <div key={i} className="p-6 border border-border/50">
                  <h3 className="font-display text-lg text-foreground">{feature.title}</h3>
                  <p className="font-body text-sm text-muted-foreground mt-2">{feature.description}</p>
                </div>
              ))}
            </div>
          </div>
        </BlockWrapper>
      );
    default:
      console.warn(`Unknown block type: ${block.type}`);
      return null;
  }
}
