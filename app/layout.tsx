import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { BottomNav } from "./(ui)/components/BottomNav";

const inter = Inter({ subsets: ["latin"], variable: "--font-sans" });

export const metadata: Metadata = {
	title: "KHHoldings Insurance",
	description: "Professional insurance solutions from Koperasi Kumpulan KH Berhad",
};

export default function RootLayout({
	children,
}: Readonly<{ children: React.ReactNode }>) {
	return (
		<html lang="en">
			<head>
				{/* Professional KHI Icon with Blue Gradient Background */}
				<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='bg' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%231e3a8a'/><stop offset='100%' style='stop-color:%233b82f6'/></linearGradient></defs><rect width='100' height='100' rx='20' fill='url(%23bg)'/><text x='50' y='65' font-size='45' font-weight='bold' font-family='Arial, sans-serif' fill='white' text-anchor='middle'>KHI</text></svg>" />
			</head>
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
