import type { HTMLAttributes } from "react";

export interface AlertProps
  extends HTMLAttributes<HTMLParagraphElement> {}

export function Alert({
  role = "alert",
  ...props
}: AlertProps) {
  return <p role={role} {...props} />;
}