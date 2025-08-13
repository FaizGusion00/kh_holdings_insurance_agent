"use client";

import { cva, type VariantProps } from "class-variance-authority";
import { twMerge } from "tailwind-merge";
import { clsx } from "clsx";

export function cn(...inputs: Array<string | undefined | null | false>) {
	return twMerge(clsx(inputs));
}

const buttonStyles = cva(
	"inline-flex items-center justify-center rounded-lg transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 btn-primary",
	{
		variants: {
			variant: {
				primary: "bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-500",
				ghost: "bg-white text-blue-700 border border-blue-200 hover:border-blue-300 hover:bg-blue-50",
				secondary: "bg-blue-100 text-blue-700 hover:bg-blue-200 focus-visible:ring-blue-500",
				outline: "border border-blue-200 text-blue-700 hover:bg-blue-50 focus-visible:ring-blue-500",
			},
			size: {
				sm: "h-9 px-4 text-sm",
				md: "h-11 px-5 text-sm font-medium",
				lg: "h-12 px-6 text-base font-medium",
			},
			loading: {
				true: "opacity-90 cursor-not-allowed",
				false: "",
			}
		},
		defaultVariants: { variant: "primary", size: "md", loading: false },
	}
);

export interface ButtonProps
	extends React.ButtonHTMLAttributes<HTMLButtonElement>,
		VariantProps<typeof buttonStyles> {
	loading?: boolean;
	loadingText?: string;
}

export function Button({ className, variant, size, loading = false, loadingText, children, ...props }: ButtonProps) {
	return (
		<button 
			className={cn(buttonStyles({ variant, size, loading }), className)} 
			disabled={loading}
			{...props}
		>
			{loading ? (
				<div className="flex items-center justify-center gap-3">
					{/* Loading Spinner */}
					<div className="relative">
						<div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
					</div>
					{/* Loading Text */}
					<span className="font-medium">{loadingText || "Loading..."}</span>
				</div>
			) : (
				children
			)}
		</button>
	);
}


