import { createRoot } from "react-dom/client";
import App from "./App.tsx";
import { WordPressProvider } from "./providers/WordPressProvider";
import "./i18n";
import "./index.css";

createRoot(document.getElementById("root")!).render(
  <WordPressProvider>
    <App />
  </WordPressProvider>
);
