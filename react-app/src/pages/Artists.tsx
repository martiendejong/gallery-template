import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { motion } from 'framer-motion';
import { artists, getArtistPieces, formatPrice } from '@/data/collections';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import AnimatedText from '@/components/AnimatedText';
import { staggerContainer, fadeUp, lineDraw } from '@/lib/animations';
import { ArrowRight } from 'lucide-react';
import { usePageBlocks } from '@/lib/wordpress-blocks';
import { BlockRenderer } from '@/components/BlockRenderer';

const Artists = () => {
  const { t } = useTranslation(['pages', 'common']);

  // WordPress integration: render blocks as page header if available
  const pageId = window.opusData?.pageId || 0;
  const { data: pageBlocks } = usePageBlocks(pageId);

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
          <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-muted-foreground block">{t('pages:artists.subtitle')}</motion.span>
          <motion.div variants={fadeUp}>
            <h1 className="font-display text-4xl md:text-6xl text-foreground mt-2"><AnimatedText text={t('pages:artists.heading')} /></h1>
          </motion.div>
          <motion.p variants={fadeUp} className="font-body text-base text-muted-foreground mt-4 max-w-lg leading-relaxed font-light">{t('pages:artists.description')}</motion.p>
        </motion.div>
        <motion.div variants={lineDraw} initial="hidden" animate="show" className="line-gold mt-12 mb-16" />

        <div className="space-y-20">
          {artists.map((artist, index) => {
            const pieces = getArtistPieces(artist.id);
            const previewPieces = pieces.slice(0, 3);
            return (
              <motion.div
                key={artist.id}
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: '-60px' }}
                transition={{ duration: 0.8, ease: [0.76, 0, 0.24, 1] }}
              >
                <div className="grid md:grid-cols-[1fr_2fr] gap-10 items-start">
                  {/* Artist info */}
                  <div className={index % 2 === 1 ? 'md:order-2' : ''}>
                    <div className="relative overflow-hidden aspect-[4/5] mb-6">
                      <motion.img src={artist.image} alt={artist.name} className="w-full h-full object-cover" whileHover={{ scale: 1.04 }} transition={{ duration: 1 }} loading="lazy" />
                      <div className="absolute inset-0 bg-gradient-to-t from-background/60 to-transparent" />
                    </div>
                    <h2 className="font-display text-3xl text-foreground">{artist.name}</h2>
                    <p className="font-accent text-sm text-primary italic mt-1">{artist.speciality}</p>
                    <p className="font-body text-sm text-muted-foreground mt-3 leading-relaxed">{artist.bio}</p>
                    <p className="font-body text-xs text-muted-foreground/60 mt-3">{pieces.length} {t('pages:artists.worksInCollection')}</p>
                  </div>

                  {/* Artist works preview */}
                  <div className={index % 2 === 1 ? 'md:order-1' : ''}>
                    <div className="grid grid-cols-3 gap-3">
                      {previewPieces.map((piece) => (
                        <Link key={piece.id} to={`/piece/${piece.id}`} className="group block">
                          <div className="relative overflow-hidden aspect-square">
                            <motion.img src={piece.images[0]} alt={piece.title} className="w-full h-full object-cover" whileHover={{ scale: 1.06 }} transition={{ duration: 0.8 }} loading="lazy" />
                            <div className="absolute inset-0 bg-background/0 group-hover:bg-background/30 transition-colors duration-500" />
                          </div>
                          <p className="font-body text-xs text-muted-foreground mt-2 truncate">{piece.title}</p>
                          {piece.price && <p className="font-body text-xs text-primary">{formatPrice(piece.price)}</p>}
                        </Link>
                      ))}
                    </div>
                    {pieces.length > 3 && (
                      <Link to="/galleries" className="group inline-flex items-center gap-2 mt-6 font-body text-xs tracking-[0.15em] uppercase text-primary hover:text-foreground transition-colors duration-500">
                        <span>{t('pages:artists.viewWorks')} ({pieces.length})</span>
                        <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform duration-300" />
                      </Link>
                    )}
                  </div>
                </div>
                {index < artists.length - 1 && <div className="line-gold mt-20" />}
              </motion.div>
            );
          })}
        </div>
      </main>
      <Footer />
    </div>
  );
};

export default Artists;
