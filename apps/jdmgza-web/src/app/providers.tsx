import type { ReactNode } from "react";

import "./api/client";

import { AuthProvider } from "@jm/auth";

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
