import type { SelectHTMLAttributes } from "react";

export interface SelectProps
  extends SelectHTMLAttributes<HTMLSelectElement> {}

export function Select({ className = "", ...props }: SelectProps) {
  const classes = ["jm-select", className].filter(Boolean).join(" ");

  return <select className={classes} {...props} />;
}
