import type { ReactNode } from "react";

import "./api/client";

import { AuthProvider } from "./auth/AuthProvider";

interface ProvidersProps {
  children: ReactNode;
}

export default function Providers({
  children,
}: ProvidersProps) {
  return (
    <AuthProvider>
      {children}
    </AuthProvider>
  );
}
