import type { TextareaHTMLAttributes } from "react";

export interface TextareaProps
  extends TextareaHTMLAttributes<HTMLTextAreaElement> {}

export function Textarea({ className = "", ...props }: TextareaProps) {
  const classes = ["jm-textarea", className].filter(Boolean).join(" ");

  return <textarea className={classes} {...props} />;
}
