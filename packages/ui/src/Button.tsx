import type { ButtonHTMLAttributes } from "react";

export interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement> {}

export function Button({
  type = "button",
  ...props
}: ButtonProps) {
  return <button type={type} {...props} />;
}