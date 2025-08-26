"use client";

import { usePathname } from "next/navigation";
import { BottomNav } from "./BottomNav";

export function ConditionalBottomNav() {
	const pathname = usePathname();
	
	// Pages where bottom navbar should NOT appear (unauthenticated pages)
	const unauthenticatedPages = [
		"/login",
		"/forgot-password",
		"/register",
		"/reset-password"
	];
	
	// Check if current page is unauthenticated
	const isUnauthenticatedPage = unauthenticatedPages.includes(pathname);
	
	// Only show bottom navbar on authenticated pages
	if (isUnauthenticatedPage) {
		return null;
	}
	
	return <BottomNav />;
}
