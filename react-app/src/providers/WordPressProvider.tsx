import React, { createContext, useContext } from 'react';

interface WordPressContextType {
  apiUrl: string;
  pageId: number;
  locale: string;
  isWordPressMode: boolean;
  themeUrl: string;
}

const WordPressContext = createContext<WordPressContextType>({
  apiUrl: '',
  pageId: 0,
  locale: 'en',
  isWordPressMode: false,
  themeUrl: '',
});

export function WordPressProvider({ children }: { children: React.ReactNode }) {
  const wpData = window.opusData;

  const value: WordPressContextType = {
    apiUrl: wpData?.apiUrl || '/wp-json/lovable/v1',
    pageId: wpData?.pageId || 0,
    locale: wpData?.locale || 'en',
    isWordPressMode: !!wpData,
    themeUrl: wpData?.themeUrl || '',
  };

  return (
    <WordPressContext.Provider value={value}>
      {children}
    </WordPressContext.Provider>
  );
}

export function useWordPress() {
  return useContext(WordPressContext);
}
