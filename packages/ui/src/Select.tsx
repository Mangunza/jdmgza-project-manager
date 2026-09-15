import type { SelectHTMLAttributes } from "react";

export interface SelectProps
  extends SelectHTMLAttributes<HTMLSelectElement> {}

export function Select(props: SelectProps) {
  return <select {...props} />;
}