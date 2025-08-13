import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { BottomNav } from "./(ui)/components/BottomNav";

const inter = Inter({ subsets: ["latin"], variable: "--font-sans" });

export const metadata: Metadata = {
	title: "WeKongsi UI Mock",
	description: "UI-only mock screens built with Next.js",
};

export default function RootLayout({
	children,
}: Readonly<{ children: React.ReactNode }>) {
	return (
		<html lang="en">
			<body className={`${inter.variable} antialiased`}>
				{/* Floating particles for background animation */}
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				<div className="floating-particle"></div>
				
				{children}
				<BottomNav />
			</body>
		</html>
	);
}
