"use client";

import { cva, type VariantProps } from "class-variance-authority";
import { twMerge } from "tailwind-merge";
import { clsx } from "clsx";

export function cn(...inputs: Array<string | undefined | null | false>) {
	return twMerge(clsx(inputs));
}

const buttonStyles = cva(
	"inline-flex items-center justify-center rounded-lg transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2",
	{
		variants: {
			variant: {
				primary: "bg-emerald-500 text-white hover:bg-emerald-600",
				ghost: "bg-white text-emerald-700 border border-emerald-200 hover:border-emerald-300",
			},
			size: {
				md: "h-11 px-5 text-sm font-medium",
				sm: "h-9 px-4 text-sm",
			},
		},
		defaultVariants: { variant: "primary", size: "md" },
	}
);

export interface ButtonProps
	extends React.ButtonHTMLAttributes<HTMLButtonElement>,
		VariantProps<typeof buttonStyles> {}

export function Button({ className, variant, size, ...props }: ButtonProps) {
	return (
		<button className={cn(buttonStyles({ variant, size }), className)} {...props} />
	);
}


