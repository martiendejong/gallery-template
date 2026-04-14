import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { motion, AnimatePresence } from 'framer-motion';
import { useState, useMemo } from 'react';
import { CATEGORIES, ArtCategory, getGalleryPieces, formatPrice } from '@/data/collections';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import Magnetic from '@/components/Magnetic';
import AnimatedText from '@/components/AnimatedText';
import { staggerContainer, fadeUp, lineDraw } from '@/lib/animations';
import { LayoutGrid, Palette, Sparkles, Box, Search, X, ArrowRight } from 'lucide-react';
import { usePageBlocks } from '@/lib/wordpress-blocks';
import { BlockRenderer } from '@/components/BlockRenderer';

const categoryIcons: Record<string, any> = {
  'All': LayoutGrid,
  'Painting': Palette,
  'Abstract': Sparkles,
  'Mixed Media': Box,
  'Figurative': LayoutGrid,
  'Print': LayoutGrid,
  'Sculpture': Box,
};

const Galleries = () => {
  const { t } = useTranslation(['pages', 'common']);

  // WordPress integration: render blocks as page header if available
  const pageId = window.opusData?.pageId || 0;
  const { data: pageBlocks } = usePageBlocks(pageId);

  const allPieces = getGalleryPieces();
  const [search, setSearch] = useState('');
  const [activeCategory, setActiveCategory] = useState<ArtCategory | 'All'>('All');
  const [searchFocused, setSearchFocused] = useState(false);

  const categoryCounts = useMemo(() => {
    const counts: Record<string, number> = {};
    allPieces.forEach(p => { counts[p.category] = (counts[p.category] || 0) + 1; });
    return counts;
  }, [allPieces]);

  const activeCategories = CATEGORIES.filter(c => categoryCounts[c] > 0);

  const filteredPieces = useMemo(() => {
    return allPieces.filter(p => {
      const matchCat = activeCategory === 'All' || p.category === activeCategory;
      const q = search.toLowerCase();
      const matchSearch = !q || p.title.toLowerCase().includes(q) || p.artist.toLowerCase().includes(q) ||
        p.keywords.some(k => k.toLowerCase().includes(q)) || p.medium.toLowerCase().includes(q);
      return matchCat && matchSearch;
    });
  }, [search, activeCategory, allPieces]);

  return (
    <div className="min-h-screen bg-background overflow-hidden">
      <Header />
      {/* WordPress blocks (page header, etc.) */}
      {pageBlocks?.layout.map((layoutItem) => {
        const block = pageBlocks.blocks.find(b => b.id === layoutItem.id);
        if (!block) return null;
        return <BlockRenderer key={block.id} block={block} />;
      })}

      <main className="pt-32 pb-24 max-w-[1400px] mx-auto px-6 md:px-12">
        <motion.div variants={staggerContainer} initial="hidden" animate="show">
          <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-muted-foreground block">{t('pages:galleries.privateCollection')}</motion.span>
          <motion.div variants={fadeUp}>
            <h1 className="font-display text-4xl md:text-6xl text-foreground mt-2"><AnimatedText text={t('pages:galleries.heading')} /></h1>
          </motion.div>
          <motion.p variants={fadeUp} className="font-body text-base text-muted-foreground mt-4 max-w-lg leading-relaxed font-light">{t('pages:galleries.description')}</motion.p>
        </motion.div>
        <motion.div variants={lineDraw} initial="hidden" animate="show" className="line-gold mt-12 mb-10" />
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.4, duration: 0.8 }} className="mb-16 space-y-6">
          <div className="relative max-w-lg">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground/40" />
            <input type="text" value={search} onChange={e => setSearch(e.target.value)} onFocus={() => setSearchFocused(true)} onBlur={() => setSearchFocused(false)}
              placeholder={t('pages:galleries.searchPlaceholder')}
              className="w-full bg-transparent border border-border/50 focus:border-primary pl-11 pr-12 py-3.5 font-body text-sm text-foreground placeholder:text-muted-foreground/40 outline-none transition-all duration-500"
            />
            <motion.div className="absolute bottom-0 left-0 h-px bg-primary" animate={{ scaleX: searchFocused ? 1 : 0 }} transition={{ duration: 0.4 }} style={{ originX: 0 }} />
            {search && <button onClick={() => setSearch('')} className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground/60 hover:text-primary transition-colors duration-300"><X className="w-4 h-4" /></button>}
          </div>
          <div className="flex flex-wrap gap-3">
            {(['All', ...activeCategories] as const).map((cat) => {
              const Icon = categoryIcons[cat] || LayoutGrid;
              return (
                <motion.button key={cat} onClick={() => setActiveCategory(cat as ArtCategory | 'All')} whileHover={{ y: -2 }} whileTap={{ scale: 0.96 }}
                  className={`font-body text-[10px] tracking-[0.2em] uppercase px-5 py-2.5 border transition-all duration-300 flex items-center gap-2 ${
                    activeCategory === cat ? 'border-primary text-background bg-primary' : 'border-border/40 text-muted-foreground hover:border-primary/50 hover:text-primary'}`}
                >
                  <Icon className="w-3.5 h-3.5" />{cat}
                  <span className="ml-1 opacity-50">{cat === 'All' ? allPieces.length : categoryCounts[cat] || 0}</span>
                </motion.button>
              );
            })}
          </div>
        </motion.div>
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="mb-8">
          <p className="font-body text-sm text-muted-foreground">
            <span className="text-primary">{filteredPieces.length}</span> {filteredPieces.length === 1 ? t('common:labels.work') : t('common:labels.works')}
            {activeCategory !== 'All' && <span> {t('pages:galleries.inCategory')} <span className="text-foreground/70">{activeCategory}</span></span>}
          </p>
        </motion.div>
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-14">
          <AnimatePresence mode="popLayout">
            {filteredPieces.map((piece, index) => (
              <motion.div key={piece.id} layout initial={{ opacity: 0, y: 40 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -20 }}
                transition={{ delay: index * 0.06, duration: 0.7, ease: [0.76, 0, 0.24, 1] }}
              >
                <Link to={`/piece/${piece.id}`} className="group block">
                  <div className="relative overflow-hidden aspect-[3/4]">
                    <motion.img src={piece.images[0]} alt={piece.title} className="w-full h-full object-cover" whileHover={{ scale: 1.06 }} transition={{ duration: 1 }} />
                    <div className="absolute inset-0 bg-background/0 group-hover:bg-background/10 transition-colors duration-700" />
                    <div className="absolute inset-0 flex items-center justify-center bg-background/0 group-hover:bg-background/40 transition-all duration-500 opacity-0 group-hover:opacity-100">
                      <span className="flex items-center gap-2 font-body text-xs tracking-[0.2em] uppercase text-foreground bg-background/70 backdrop-blur-sm px-4 py-2.5 border border-primary/40">
                        {t('common:labels.viewDetails')}<ArrowRight className="w-3.5 h-3.5 text-primary" />
                      </span>
                    </div>
                    {piece.sold && <span className="absolute top-3 right-3 font-body text-[8px] tracking-[0.2em] uppercase px-2.5 py-1 bg-destructive/90 text-destructive-foreground backdrop-blur-sm">{t('common:labels.sold')}</span>}
                    <span className="absolute top-3 left-3 font-body text-[8px] tracking-[0.2em] uppercase px-2.5 py-1 bg-background/80 text-primary backdrop-blur-sm">{piece.category}</span>
                  </div>
                  <div className="mt-4">
                    <h3 className="font-display text-xl text-foreground group-hover:text-primary transition-colors duration-500">{piece.title}</h3>
                    <p className="font-accent text-sm text-muted-foreground mt-1 italic">{piece.artist}, {piece.year}</p>
                    {piece.price && !piece.sold && <p className="font-body text-sm text-primary mt-2">{formatPrice(piece.price)}</p>}
                    {piece.sold && <p className="font-body text-sm text-muted-foreground/60 mt-2 line-through">{piece.price ? formatPrice(piece.price) : ''}</p>}
                  </div>
                </Link>
              </motion.div>
            ))}
          </AnimatePresence>
          {filteredPieces.length === 0 && (
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="col-span-full text-center py-20">
              <p className="font-display text-2xl text-muted-foreground/30">{t('pages:galleries.noWorksFound')}</p>
            </motion.div>
          )}
        </div>
      </main>
      <Footer />
    </div>
  );
};

export default Galleries;
