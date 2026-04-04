import { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { galleries } from '@/data/collections';
import heroImage from '@/assets/hero-gallery.jpg';
import { usePageBlocks } from '@/lib/wordpress-blocks';
import { BlockWrapper } from '@/components/BlockWrapper';

const defaultSlideImages = [...galleries.map((g) => g.coverImage), heroImage];

const kenBurnsVariants = [
  { scale: 1.15, x: '-3%', y: '-2%' },
  { scale: 1.2, x: '3%', y: '-3%' },
  { scale: 1.18, x: '-2%', y: '3%' },
  { scale: 1.15, x: '2%', y: '2%' },
  { scale: 1.2, x: '-3%', y: '-1%' },
];

interface HeroSlideshowProps {
  className?: string;
}

const HeroSlideshow = ({ className = '' }: HeroSlideshowProps) => {
  const [currentIndex, setCurrentIndex] = useState(0);

  // WordPress integration: fetch hero block data if available
  const pageId = window.opusData?.pageId || 0;
  const { data: pageData } = usePageBlocks(pageId);
  const heroBlock = pageData?.blocks.find(b => b.type === 'hero');

  // Use WordPress images if available, otherwise use defaults
  const slideImages = heroBlock?.data?.backgroundImage
    ? [heroBlock.data.backgroundImage, ...defaultSlideImages]
    : defaultSlideImages;

  const advanceSlide = useCallback(() => {
    setCurrentIndex((prev) => (prev + 1) % slideImages.length);
  }, [slideImages.length]);

  useEffect(() => {
    const interval = setInterval(advanceSlide, 5000);
    return () => clearInterval(interval);
  }, [advanceSlide]);

  const kenBurns = kenBurnsVariants[currentIndex % kenBurnsVariants.length];

  const content = (
    <div className={`relative overflow-hidden ${className}`}>
      <AnimatePresence mode="sync">
        <motion.div
          key={currentIndex}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 1.5, ease: [0.25, 0.1, 0.25, 1] }}
          className="absolute inset-0"
        >
          <motion.img
            src={slideImages[currentIndex]}
            alt=""
            className="w-full h-full object-cover will-change-transform"
            initial={{ scale: 1.05, x: '0%', y: '0%' }}
            animate={{
              scale: kenBurns.scale,
              x: kenBurns.x,
              y: kenBurns.y,
            }}
            transition={{
              duration: 8,
              ease: 'linear',
            }}
          />
        </motion.div>
      </AnimatePresence>
    </div>
  );

  if (heroBlock) {
    return (
      <BlockWrapper blockId={heroBlock.id} blockType="hero">
        {content}
      </BlockWrapper>
    );
  }

  return content;
};

export default HeroSlideshow;
