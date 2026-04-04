import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { motion, useScroll, useTransform } from 'framer-motion';
import { useRef } from 'react';
import { ChevronDown, ArrowRight } from 'lucide-react';
import opusHero from '@/assets/opus-hero.jpg';
import opusLogoWhite from '@/assets/opus-logo-white.png';
import { artists, getGalleryPieces, getDepotPieces, formatPrice } from '@/data/collections';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import CinematicHero from '@/components/CinematicHero';
import Magnetic from '@/components/Magnetic';
import AnimatedText from '@/components/AnimatedText';
import Parallax from '@/components/Parallax';
import AnimatedCounter from '@/components/AnimatedCounter';
import { staggerContainer, fadeUp, lineDraw, staggerContainerSlow } from '@/lib/animations';
import { usePageBlocks } from '@/lib/wordpress-blocks';
import { BlockRenderer } from '@/components/BlockRenderer';

const Index = () => {
  const { t } = useTranslation(['pages', 'common']);

  // WordPress integration: if blocks are available, render them
  const pageId = window.opusData?.pageId || 0;
  const { data: pageBlocks } = usePageBlocks(pageId);

  if (pageBlocks?.blocks && pageBlocks.blocks.length > 0) {
    return (
      <div className="min-h-screen bg-background overflow-hidden">
        <Header />
        {pageBlocks.layout.map((layoutItem) => {
          const block = pageBlocks.blocks.find(b => b.id === layoutItem.id);
          if (!block) return null;
          return <BlockRenderer key={block.id} block={block} />;
        })}
        <Footer />
      </div>
    );
  }

  // Default: render the static content
  const galleryPieces = getGalleryPieces();
  const depotPieces = getDepotPieces();
  const featuredPieces = galleryPieces.slice(0, 6);
  const depotPreview = depotPieces.slice(0, 4);

  const heroRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress: heroScroll } = useScroll({
    target: heroRef,
    offset: ['start start', 'end start'],
  });
  const heroY = useTransform(heroScroll, [0, 1], [0, 200]);
  const heroScale = useTransform(heroScroll, [0, 1], [1, 1.15]);
  const heroOpacity = useTransform(heroScroll, [0, 0.8], [1, 0]);
  const textY = useTransform(heroScroll, [0, 1], [0, -80]);

  return (
    <div className="min-h-screen bg-background overflow-hidden">
      <Header />

      {/* Hero Section */}
      <section ref={heroRef} className="relative h-screen overflow-hidden">
        <motion.div className="absolute inset-0" style={{ y: heroY, scale: heroScale }}>
          <CinematicHero imageSrc={opusHero} />
        </motion.div>
        <motion.div
          style={{ y: textY, opacity: heroOpacity }}
          className="relative z-10 h-full flex flex-col items-center justify-center text-center px-6 md:px-12"
        >
          <motion.div variants={staggerContainerSlow} initial="hidden" animate="show" className="flex flex-col items-center">
            <motion.span variants={fadeUp} className="font-accent text-sm md:text-base tracking-[0.4em] uppercase text-primary/80 block">
              {t('common:brand.est')}
            </motion.span>
            <motion.img
              variants={fadeUp}
              src={opusLogoWhite}
              alt="OPUS Art Gallery"
              className="h-32 md:h-48 lg:h-56 w-auto mt-4 drop-shadow-lg cursor-pointer"
              initial={{ opacity: 0.4 }}
              animate={{ opacity: 0.4 }}
              whileHover={{ opacity: 1, scale: 1.15 }}
              transition={{ duration: 0.5, ease: [0.76, 0, 0.24, 1] }}
            />
            <motion.div
              initial={{ width: 0, opacity: 0 }}
              animate={{ width: 120, opacity: 1 }}
              transition={{ duration: 1.2, delay: 1.8, ease: [0.76, 0, 0.24, 1] }}
              className="h-px bg-primary mt-8"
            />
            <motion.p variants={fadeUp} className="font-accent text-base md:text-lg text-foreground/60 mt-6 italic tracking-wide max-w-lg">
              {t('pages:index.heroSubtitle')}
            </motion.p>
            <motion.div variants={fadeUp} className="mt-10">
              <Magnetic strength={0.2}>
                <Link to="/galleries" className="group relative inline-flex items-center gap-4 px-10 py-4 overflow-hidden">
                  <motion.div className="absolute inset-0 border border-primary/50" />
                  <motion.div
                    className="absolute inset-0 bg-primary/10"
                    initial={{ scaleX: 0 }}
                    whileHover={{ scaleX: 1 }}
                    transition={{ duration: 0.5, ease: [0.76, 0, 0.24, 1] }}
                    style={{ originX: 0 }}
                  />
                  <span className="relative z-10 font-body text-xs tracking-[0.25em] uppercase text-primary group-hover:text-foreground transition-colors duration-500">
                    {t('pages:index.viewCollection')}
                  </span>
                  <motion.span className="relative z-10 inline-block w-6 h-px bg-primary group-hover:bg-foreground transition-colors duration-500" whileHover={{ width: 32 }} />
                </Link>
              </Magnetic>
            </motion.div>
          </motion.div>
        </motion.div>
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 3, duration: 1 }}
          className="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 flex flex-col items-center gap-2"
        >
          <span className="font-body text-[10px] tracking-[0.3em] uppercase text-muted-foreground/60">{t('pages:index.scroll')}</span>
          <motion.div animate={{ y: [0, 8, 0] }} transition={{ duration: 2, repeat: Infinity, ease: 'easeInOut' }}>
            <ChevronDown className="w-4 h-4 text-primary/60" />
          </motion.div>
        </motion.div>
      </section>

      {/* Featured Gallery Works with Prices */}
      <section className="max-w-[1400px] mx-auto px-6 md:px-12 py-24 md:py-32">
        <motion.div variants={staggerContainer} initial="hidden" whileInView="show" viewport={{ once: true, margin: '-100px' }}>
          <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-muted-foreground block">
            {t('pages:index.curatedGalleries')}
          </motion.span>
          <motion.div variants={fadeUp}>
            <h2 className="font-display text-3xl md:text-5xl text-foreground mt-2">
              <AnimatedText text={t('pages:index.distinguishedWorks')} />
            </h2>
          </motion.div>
        </motion.div>
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-14 mt-14">
          {featuredPieces.map((piece, index) => (
            <motion.div
              key={piece.id}
              initial={{ opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: '-50px' }}
              transition={{ duration: 0.8, delay: index * 0.1, ease: [0.76, 0, 0.24, 1] }}
            >
              <Link to={`/piece/${piece.id}`} className="group block">
                <div className="relative overflow-hidden aspect-[3/4]">
                  <motion.img
                    src={piece.images[0]}
                    alt={piece.title}
                    className="w-full h-full object-cover"
                    whileHover={{ scale: 1.06 }}
                    transition={{ duration: 1 }}
                  />
                  <div className="absolute inset-0 bg-background/0 group-hover:bg-background/20 transition-colors duration-500" />
                  {piece.sold && (
                    <span className="absolute top-3 right-3 font-body text-[9px] tracking-[0.2em] uppercase px-3 py-1 bg-destructive/90 text-destructive-foreground backdrop-blur-sm">
                      {t('common:labels.sold')}
                    </span>
                  )}
                  <div className="absolute top-3 left-3 w-5 h-5 border-t border-l border-primary/0 group-hover:border-primary/50 transition-all duration-700" />
                  <div className="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-primary/0 group-hover:border-primary/50 transition-all duration-700" />
                </div>
                <div className="mt-4">
                  <h3 className="font-display text-lg text-foreground group-hover:text-primary transition-colors duration-500">{piece.title}</h3>
                  <p className="font-accent text-sm text-muted-foreground mt-1 italic">{piece.artist}, {piece.year}</p>
                  {piece.price && !piece.sold && (
                    <p className="font-body text-sm text-primary mt-2 tracking-wide">{formatPrice(piece.price)}</p>
                  )}
                </div>
              </Link>
            </motion.div>
          ))}
        </div>
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="text-center mt-16"
        >
          <Magnetic strength={0.2}>
            <Link to="/galleries" className="group inline-flex items-center gap-3 font-body text-sm tracking-[0.2em] uppercase text-primary hover:text-foreground transition-colors duration-500">
              <span>{t('pages:index.enterCollection')}</span>
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
            </Link>
          </Magnetic>
        </motion.div>
      </section>

      {/* Artists Section */}
      <section className="border-t border-border/50 overflow-hidden">
        <div className="max-w-[1400px] mx-auto px-6 md:px-12 py-24 md:py-32">
          <motion.div variants={staggerContainer} initial="hidden" whileInView="show" viewport={{ once: true }}>
            <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-primary block">
              {t('pages:index.artistsSection')}
            </motion.span>
            <motion.h2 variants={fadeUp} className="font-display text-3xl md:text-5xl text-foreground mt-3">
              <AnimatedText text={t('pages:index.bugattiHeritage')} />
            </motion.h2>
            <motion.div variants={lineDraw} className="line-gold mt-8 mb-14" />
          </motion.div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {artists.slice(0, 4).map((artist, i) => (
              <motion.div
                key={artist.id}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1, duration: 0.8 }}
              >
                <Link to={`/artists`} className="group block">
                  <div className="relative overflow-hidden aspect-square">
                    <motion.img
                      src={artist.image}
                      alt={artist.name}
                      className="w-full h-full object-cover"
                      whileHover={{ scale: 1.06 }}
                      transition={{ duration: 1 }}
                      loading="lazy"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-background/80 via-transparent to-transparent" />
                    <div className="absolute bottom-0 left-0 right-0 p-5">
                      <h3 className="font-display text-xl text-foreground">{artist.name}</h3>
                      <p className="font-body text-xs text-muted-foreground mt-1">{artist.speciality}</p>
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
          <motion.div initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} viewport={{ once: true }} className="text-center mt-12">
            <Link to="/artists" className="group inline-flex items-center gap-3 font-body text-sm tracking-[0.2em] uppercase text-primary hover:text-foreground transition-colors duration-500">
              <span>{t('pages:index.viewAllArtists')}</span>
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
            </Link>
          </motion.div>
        </div>
      </section>

      {/* Philosophy Statement */}
      <section className="border-t border-border/50 overflow-hidden">
        <div className="max-w-[1400px] mx-auto px-6 md:px-12 py-24 md:py-32">
          <motion.div variants={staggerContainerSlow} initial="hidden" whileInView="show" viewport={{ once: true, margin: '-100px' }} className="max-w-2xl mx-auto text-center">
            <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-primary block">
              {t('pages:index.philosophy')}
            </motion.span>
            <Parallax speed={0.15}>
              <motion.blockquote variants={fadeUp} className="font-display text-2xl md:text-3xl text-foreground mt-6 leading-relaxed italic">
                {t('pages:index.philosophyQuote')}
              </motion.blockquote>
            </Parallax>
            <motion.div variants={lineDraw} className="w-16 h-px bg-primary mx-auto mt-8 mb-4" />
            <motion.p variants={fadeUp} className="font-body text-sm text-muted-foreground tracking-[0.1em]">
              {t('pages:index.philosophyAttribution')}
            </motion.p>
          </motion.div>
        </div>
      </section>

      {/* Depot Preview */}
      <section className="border-t border-border/50 overflow-hidden">
        <div className="max-w-[1400px] mx-auto px-6 md:px-12 py-24 md:py-32">
          <motion.div variants={staggerContainer} initial="hidden" whileInView="show" viewport={{ once: true }}>
            <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-primary block">
              {t('pages:index.depotSection')}
            </motion.span>
            <motion.h2 variants={fadeUp} className="font-display text-3xl md:text-5xl text-foreground mt-3">
              <AnimatedText text={t('pages:index.atExhibition')} />
            </motion.h2>
            <motion.p variants={fadeUp} className="font-body text-base text-muted-foreground mt-4 max-w-xl leading-relaxed font-light">
              {t('pages:index.qrDescription')}
            </motion.p>
          </motion.div>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-14">
            {depotPreview.map((piece, i) => (
              <motion.div
                key={piece.id}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1, duration: 0.8 }}
              >
                <Link to={`/piece/${piece.id}`} className="group block">
                  <div className="relative overflow-hidden aspect-square">
                    <motion.img src={piece.images[0]} alt={piece.title} className="w-full h-full object-cover" whileHover={{ scale: 1.06 }} transition={{ duration: 1 }} loading="lazy" />
                    <div className="absolute inset-0 bg-background/0 group-hover:bg-background/20 transition-colors duration-500" />
                  </div>
                  <div className="mt-3">
                    <h3 className="font-display text-base text-foreground group-hover:text-primary transition-colors duration-500 truncate">{piece.title}</h3>
                    <p className="font-accent text-xs text-muted-foreground mt-0.5 italic">{piece.artist}</p>
                    {piece.price && <p className="font-body text-xs text-primary mt-1">{formatPrice(piece.price)}</p>}
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
          <motion.div initial={{ opacity: 0 }} whileInView={{ opacity: 1 }} viewport={{ once: true }} className="text-center mt-12">
            <Link to="/depot" className="group inline-flex items-center gap-3 font-body text-sm tracking-[0.2em] uppercase text-primary hover:text-foreground transition-colors duration-500">
              <span>{t('pages:index.viewDepot')}</span>
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
            </Link>
          </motion.div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0">
          <motion.img
            src={opusHero}
            alt=""
            className="w-full h-full object-cover"
            initial={{ scale: 1.1 }}
            whileInView={{ scale: 1 }}
            viewport={{ once: true }}
            transition={{ duration: 2, ease: [0.25, 0.1, 0.25, 1] }}
            loading="lazy"
          />
          <div className="absolute inset-0 bg-background/75" />
        </div>
        <div className="relative z-10 max-w-[1400px] mx-auto px-6 md:px-12 py-36 md:py-48">
          <motion.div variants={staggerContainerSlow} initial="hidden" whileInView="show" viewport={{ once: true, margin: '-80px' }} className="max-w-3xl">
            <motion.span variants={fadeUp} className="font-accent text-sm tracking-[0.3em] uppercase text-primary block">
              {t('pages:index.byInvitation')}
            </motion.span>
            <motion.div variants={fadeUp}>
              <h2 className="font-display text-4xl md:text-6xl lg:text-7xl text-foreground mt-4 leading-[0.95]">
                {t('pages:index.aPrivate')}<br />
                <span className="gold-text-gradient">{t('pages:index.experience')}</span>
              </h2>
            </motion.div>
            <motion.p variants={fadeUp} className="font-body text-base md:text-lg text-muted-foreground mt-8 max-w-xl leading-relaxed font-light">
              {t('pages:index.ctaDescription')}
            </motion.p>
            <motion.div variants={fadeUp} className="flex items-center gap-10 mt-12 pb-12 border-b border-border/30">
              {[
                { value: artists.length, label: t('pages:index.statGalleries') },
                { value: galleryPieces.length + depotPieces.length, label: t('pages:index.statMasterworks') },
                { value: galleryPieces.length, label: t('pages:index.statArtists') },
              ].map((stat) => (
                <div key={stat.label}>
                  <AnimatedCounter end={stat.value} className="font-display text-3xl md:text-4xl text-primary block" />
                  <span className="font-body text-xs text-muted-foreground tracking-[0.15em] uppercase mt-1 block">{stat.label}</span>
                </div>
              ))}
            </motion.div>
            <motion.div variants={fadeUp} className="flex flex-col sm:flex-row items-start sm:items-center gap-6 mt-12">
              <Magnetic strength={0.3}>
                <Link to="/galleries" className="group relative inline-flex items-center gap-4 px-10 py-5 overflow-hidden">
                  <motion.div className="absolute inset-0 border border-primary" />
                  <motion.div className="absolute inset-0 bg-primary" initial={{ scaleX: 0 }} whileHover={{ scaleX: 1 }} transition={{ duration: 0.5, ease: [0.76, 0, 0.24, 1] }} style={{ originX: 0 }} />
                  <span className="relative z-10 font-body text-sm tracking-[0.2em] uppercase text-primary group-hover:text-background transition-colors duration-500">
                    {t('pages:index.enterCollection')}
                  </span>
                </Link>
              </Magnetic>
              <Magnetic strength={0.2}>
                <Link to="/about" className="group inline-flex items-center gap-3 font-body text-sm tracking-[0.2em] uppercase text-muted-foreground hover:text-foreground transition-colors duration-500">
                  <span>{t('pages:index.ourLegacy')}</span>
                  <motion.span className="inline-block w-6 h-px bg-muted-foreground group-hover:bg-foreground transition-colors duration-500" whileHover={{ width: 32 }} />
                </Link>
              </Magnetic>
            </motion.div>
          </motion.div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default Index;
