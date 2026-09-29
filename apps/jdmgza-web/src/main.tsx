import { StrictMode } from "react";
import { createRoot } from "react-dom/client";

import App from "./app/App";
import Providers from "./app/providers";

import "@jm/theme/styles.css";

import "./styles/global.css";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <Providers>
      <App />
    </Providers>
  </StrictMode>,
);
