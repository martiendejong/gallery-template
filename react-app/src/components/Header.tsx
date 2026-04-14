import { Link, useLocation } from 'react-router-dom';
import opusLogo from '@/assets/opus-logo.png';
import { motion, AnimatePresence, useScroll, useMotionValueEvent } from 'framer-motion';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import Magnetic from './Magnetic';
import { staggerContainerSlow, fadeUp, lineDraw } from '@/lib/animations';
import { artists, getAllPieces } from '@/data/collections';
import { useWordPress } from '@/providers/WordPressProvider';

const FormField = ({ label, type, value, onChange, placeholder }: {
  label: string; type: string; value: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void; placeholder: string;
}) => {
  const [focused, setFocused] = useState(false);
  return (
    <div className="space-y-2">
      <label className="font-body text-xs tracking-[0.2em] uppercase text-muted-foreground block">{label}</label>
      <div className="relative">
        <input
          type={type} value={value} onChange={onChange} placeholder={placeholder}
          onFocus={() => setFocused(true)} onBlur={() => setFocused(false)}
          className="w-full bg-transparent border border-border/50 focus:border-primary px-4 py-3.5 font-body text-sm text-foreground placeholder:text-muted-foreground/40 outline-none transition-all duration-500"
        />
        <motion.div
          className="absolute bottom-0 left-0 h-px bg-primary"
          animate={{ scaleX: focused ? 1 : 0 }}
          transition={{ duration: 0.4 }}
          style={{ originX: 0 }}
        />
      </div>
    </div>
  );
};

const SignInModal = ({ onClose }: { onClose: () => void }) => {
  const { t } = useTranslation('common');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [mode, setMode] = useState<'signin' | 'register'>('signin');

  return (
    <motion.div
      initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
      transition={{ duration: 0.4 }}
      className="fixed inset-0 z-[100] flex items-center justify-center px-6"
      onClick={onClose}
    >
      <div className="absolute inset-0 bg-background/90 backdrop-blur-xl" />
      <motion.div
        initial={{ opacity: 0, y: 30, scale: 0.97 }}
        animate={{ opacity: 1, y: 0, scale: 1 }}
        exit={{ opacity: 0, y: 20, scale: 0.97 }}
        transition={{ duration: 0.5, ease: [0.76, 0, 0.24, 1] }}
        className="relative z-10 w-full max-w-md bg-card/50 border border-border/40 p-10 md:p-12 backdrop-blur-sm"
        onClick={e => e.stopPropagation()}
      >
        <div className="absolute top-3 left-3 w-5 h-5 border-t border-l border-primary/40" />
        <div className="absolute top-3 right-3 w-5 h-5 border-t border-r border-primary/40" />
        <div className="absolute bottom-3 left-3 w-5 h-5 border-b border-l border-primary/40" />
        <div className="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-primary/40" />
        <button onClick={onClose} className="absolute top-5 right-5 font-body text-xs tracking-[0.2em] uppercase text-muted-foreground hover:text-primary transition-colors duration-300">✕</button>
        <motion.div variants={staggerContainerSlow} initial="hidden" animate="show">
          <div className="text-center mb-10">
            <motion.span variants={fadeUp} className="font-accent text-xs tracking-[0.3em] uppercase text-muted-foreground block">{t('signInModal.title')}</motion.span>
            <motion.h2 variants={fadeUp} className="font-display text-3xl text-foreground mt-2">{mode === 'signin' ? t('signInModal.signIn') : t('signInModal.joinCollection')}</motion.h2>
            <motion.div variants={lineDraw} className="w-12 h-px bg-primary mx-auto mt-5" />
          </div>
          <motion.form variants={fadeUp} className="space-y-5" onSubmit={e => e.preventDefault()}>
            {mode === 'register' && <FormField label={t('signInModal.fullName')} type="text" value="" onChange={() => {}} placeholder={t('signInModal.fullNamePlaceholder')} />}
            <FormField label={t('signInModal.emailAddress')} type="email" value={email} onChange={e => setEmail(e.target.value)} placeholder="your@email.com" />
            <FormField label={t('signInModal.password')} type="password" value={password} onChange={e => setPassword(e.target.value)} placeholder="••••••••" />
            <motion.button type="submit" className="group relative w-full overflow-hidden py-4 mt-2" whileHover="hover" whileTap={{ scale: 0.98 }}>
              <motion.div className="absolute inset-0 border border-primary" />
              <motion.div className="absolute inset-0 bg-primary" initial={{ scaleX: 0 }} variants={{ hover: { scaleX: 1 } }} transition={{ duration: 0.5, ease: [0.76, 0, 0.24, 1] }} style={{ originX: 0 }} />
              <span className="relative z-10 font-body text-sm tracking-[0.2em] uppercase text-primary group-hover:text-background transition-colors duration-500">
                {mode === 'signin' ? t('signInModal.enterCollection') : t('signInModal.createAccount')}
              </span>
            </motion.button>
          </motion.form>
          <motion.div variants={fadeUp} className="text-center mt-7">
            <span className="font-body text-sm text-muted-foreground">
              {mode === 'signin' ? (
                <>{t('signInModal.notMember')}{' '}<button onClick={() => setMode('register')} className="text-primary hover:text-foreground transition-colors duration-300">{t('signInModal.joinCollection')}</button></>
              ) : (
                <>{t('signInModal.alreadyMember')}{' '}<button onClick={() => setMode('signin')} className="text-primary hover:text-foreground transition-colors duration-300">{t('signInModal.signIn')}</button></>
              )}
            </span>
          </motion.div>
        </motion.div>
      </motion.div>
    </motion.div>
  );
};

const MobileMenu = ({ links, onClose, onSignIn }: { links: { to: string; label: string; active: boolean }[]; onClose: () => void; onSignIn: () => void }) => {
  const { t } = useTranslation('common');
  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} transition={{ duration: 0.4 }} className="fixed inset-0 z-[90] flex flex-col">
      <div className="absolute inset-0 bg-background/95 backdrop-blur-2xl" />
      <div className="relative z-10 flex justify-end px-6 pt-7">
        <button onClick={onClose} className="font-body text-xs tracking-[0.2em] uppercase text-muted-foreground hover:text-primary transition-colors duration-300">✕ {t('header.close')}</button>
      </div>
      <nav className="relative z-10 flex-1 flex flex-col items-center justify-center gap-2">
        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.05, duration: 0.5 }} className="mb-6">
          <span className="font-body text-xs text-muted-foreground tracking-[0.15em]">{artists.length} {t('header.galleries')} · {getAllPieces().length} {t('header.works')}</span>
        </motion.div>
        {links.map((link, i) => (
          <motion.div key={link.to} initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 + i * 0.08, duration: 0.6, ease: [0.76, 0, 0.24, 1] }}>
            <Link to={link.to} onClick={onClose} className={`font-display text-3xl md:text-4xl tracking-[0.02em] transition-colors duration-500 block py-3 ${link.active ? 'text-primary' : 'text-foreground/60 hover:text-foreground'}`}>
              {link.label}
            </Link>
          </motion.div>
        ))}
        <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 + links.length * 0.08, duration: 0.6 }} className="mt-8">
          <button onClick={() => { onClose(); onSignIn(); }} className="font-body text-xs tracking-[0.2em] uppercase text-primary border border-primary/40 px-8 py-3 hover:bg-primary hover:text-background transition-all duration-500">
            {t('header.signIn')}
          </button>
        </motion.div>
      </nav>
      <motion.div initial={{ scaleX: 0 }} animate={{ scaleX: 1 }} transition={{ delay: 0.5, duration: 1, ease: [0.76, 0, 0.24, 1] }} className="relative z-10 mx-6 mb-8 line-gold" style={{ originX: 0 }} />
    </motion.div>
  );
};

const Header = () => {
  const location = useLocation();
  const isHome = location.pathname === '/';
  const { scrollY } = useScroll();
  const [scrolled, setScrolled] = useState(false);
  const [signInOpen, setSignInOpen] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { t, i18n } = useTranslation('common');
  const { isWordPressMode } = useWordPress();

  useMotionValueEvent(scrollY, 'change', (latest) => setScrolled(latest > 50));

  const headerBg = scrolled ? 'bg-background/90 backdrop-blur-xl' : 'bg-transparent';
  const headerBorder = scrolled ? 'border-b border-border/30' : 'border-b border-transparent';

  const navLinks = [
    { to: '/', label: t('nav.home'), active: isHome },
    { to: '/galleries', label: t('nav.collection'), active: location.pathname === '/galleries' },
    { to: '/depot', label: t('nav.depot'), active: location.pathname === '/depot' },
    { to: '/artists', label: t('nav.artists'), active: location.pathname === '/artists' },
    { to: '/about', label: t('nav.about'), active: location.pathname === '/about' },
    { to: '/contact', label: t('nav.contact'), active: location.pathname === '/contact' },
  ];

  return (
    <>
      <motion.header
        initial={{ opacity: 0, y: -30 }} animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 1.2, ease: [0.76, 0, 0.24, 1], delay: 0.5 }}
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-700 ${headerBg} ${headerBorder}`}
      >
        <div className="max-w-[1400px] mx-auto px-6 md:px-12 flex items-center justify-between h-20">
          <Magnetic strength={0.15}>
            <Link to="/" className="group">
              <motion.div
                initial={{ opacity: 0, x: -10 }} animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.9, duration: 0.6 }}
              >
                <motion.img
                  src={opusLogo}
                  alt="OPUS Art Gallery"
                  className="h-14 md:h-16 w-auto"
                  whileHover={{
                    scale: 1.35,
                    filter: [
                      'brightness(1) drop-shadow(0 0 0px rgba(255,255,255,0))',
                      'brightness(1.3) drop-shadow(0 0 8px rgba(255,255,255,0.4))',
                      'brightness(1) drop-shadow(0 0 0px rgba(255,255,255,0))',
                    ],
                  }}
                  transition={{
                    scale: { duration: 0.4, ease: [0.76, 0, 0.24, 1] },
                    filter: { duration: 1, ease: 'easeInOut' },
                  }}
                />
              </motion.div>
            </Link>
          </Magnetic>
          <nav className="hidden lg:flex items-center gap-6">
            {navLinks.map((link, i) => (
              <Magnetic key={link.to} strength={0.2}>
                <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 1 + i * 0.08, duration: 0.5 }}>
                  <Link to={link.to} className={`relative font-body text-xs tracking-[0.15em] uppercase transition-colors duration-500 ${link.active ? 'text-primary' : 'text-muted-foreground hover:text-foreground'}`}>
                    {link.label}
                    {link.active && <motion.div layoutId="nav-indicator" className="absolute -bottom-1 left-0 right-0 h-px bg-primary" transition={{ type: 'spring', stiffness: 300, damping: 30 }} />}
                  </Link>
                </motion.div>
              </Magnetic>
            ))}
          </nav>
          <div className="flex items-center gap-5">
            <button
              onClick={() => i18n.changeLanguage(i18n.language === 'en' ? 'nl' : 'en')}
              className="hidden lg:flex items-center gap-1 font-body text-[10px] tracking-[0.15em] uppercase text-muted-foreground hover:text-primary transition-colors duration-500"
            >
              <span className={i18n.language === 'en' ? 'text-primary' : ''}>EN</span>
              <span className="text-border">/</span>
              <span className={i18n.language === 'nl' ? 'text-primary' : ''}>NL</span>
            </button>
            <Magnetic strength={0.2}>
              <motion.button
                initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 1.5, duration: 0.5 }}
                onClick={() => setSignInOpen(true)}
                className="group relative hidden lg:inline-flex items-center gap-3 px-6 py-2 overflow-hidden"
                whileHover="hover"
              >
                <motion.div className="absolute inset-0 border border-border/40 group-hover:border-primary transition-colors duration-500" />
                <motion.div className="absolute inset-0 bg-primary" initial={{ scaleX: 0 }} variants={{ hover: { scaleX: 1 } }} transition={{ duration: 0.4, ease: [0.76, 0, 0.24, 1] }} style={{ originX: 0 }} />
                <span className="relative z-10 font-body text-xs tracking-[0.2em] uppercase text-muted-foreground group-hover:text-background transition-colors duration-400">
                  {t('header.signIn')}
                </span>
              </motion.button>
            </Magnetic>
            <motion.button
              initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1.2, duration: 0.5 }}
              onClick={() => setMobileMenuOpen(true)}
              className="lg:hidden flex flex-col items-end gap-1.5 py-2" aria-label="Open menu"
            >
              <motion.span className="block w-6 h-px bg-foreground" />
              <motion.span className="block w-4 h-px bg-foreground" />
              <motion.span className="block w-5 h-px bg-foreground" />
            </motion.button>
          </div>
        </div>
      </motion.header>
      <AnimatePresence>{signInOpen && <SignInModal onClose={() => setSignInOpen(false)} />}</AnimatePresence>
      <AnimatePresence>{mobileMenuOpen && <MobileMenu links={navLinks} onClose={() => setMobileMenuOpen(false)} onSignIn={() => setSignInOpen(true)} />}</AnimatePresence>
    </>
  );
};

export default Header;
