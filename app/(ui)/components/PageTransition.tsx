"use client";

import { motion } from "framer-motion";
import { ReactNode } from "react";

interface PageTransitionProps {
	children: ReactNode;
	className?: string;
}

// Page entrance animation
export function PageTransition({ children, className = "" }: PageTransitionProps) {
	return (
		<motion.div
			className={className}
			initial={{ opacity: 0, y: 20 }}
			animate={{ opacity: 1, y: 0 }}
			exit={{ opacity: 0, y: -20 }}
			transition={{
				duration: 0.6,
				ease: [0.25, 0.46, 0.45, 0.94]
			}}
		>
			{children}
		</motion.div>
	);
}

// Staggered children animation
export function StaggeredContainer({ children, className = "" }: PageTransitionProps) {
	return (
		<motion.div
			className={className}
			initial="hidden"
			animate="visible"
			variants={{
				hidden: { opacity: 0 },
				visible: {
					opacity: 1,
					transition: {
						staggerChildren: 0.1,
						delayChildren: 0.2
					}
				}
			}}
		>
			{children}
		</motion.div>
	);
}

// Staggered item animation
export function StaggeredItem({ children, className = "" }: PageTransitionProps) {
	return (
		<motion.div
			className={className}
			variants={{
				hidden: { opacity: 0, y: 20, scale: 0.95 },
				visible: {
					opacity: 1,
					y: 0,
					scale: 1,
					transition: {
						duration: 0.5,
						ease: [0.25, 0.46, 0.45, 0.94]
					}
				}
			}}
		>
			{children}
		</motion.div>
	);
}

// Fade in animation
export function FadeIn({ children, className = "", delay = 0 }: PageTransitionProps & { delay?: number }) {
	return (
		<motion.div
			className={className}
			initial={{ opacity: 0 }}
			animate={{ opacity: 1 }}
			transition={{
				duration: 0.8,
				delay,
				ease: [0.25, 0.46, 0.45, 0.94]
			}}
		>
			{children}
		</motion.div>
	);
}

// Slide up animation
export function SlideUp({ children, className = "", delay = 0 }: PageTransitionProps & { delay?: number }) {
	return (
		<motion.div
			className={className}
			initial={{ opacity: 0, y: 30 }}
			animate={{ opacity: 1, y: 0 }}
			transition={{
				duration: 0.6,
				delay,
				ease: [0.25, 0.46, 0.45, 0.94]
			}}
		>
			{children}
		</motion.div>
	);
}

// Scale in animation
export function ScaleIn({ children, className = "", delay = 0 }: PageTransitionProps & { delay?: number }) {
	return (
		<motion.div
			className={className}
			initial={{ opacity: 0, scale: 0.9 }}
			animate={{ opacity: 1, scale: 1 }}
			transition={{
				duration: 0.5,
				delay,
				ease: [0.25, 0.46, 0.45, 0.94]
			}}
		>
			{children}
		</motion.div>
	);
}
