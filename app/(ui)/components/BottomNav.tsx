"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Home, Compass, FileText, Hospital, UserRound, ChevronUp } from "lucide-react";

const items = [
	{ href: "/dashboard", icon: Home, label: "Dashboard" },
	{ href: "/explore", icon: Compass, label: "Explore" },
	{ href: "#", icon: FileText, label: "Docs", disabled: true }, // Disabled - does nothing
	{ href: "/hospitals", icon: Hospital, label: "Hospitals" },
	{ href: "/profile", icon: UserRound, label: "Profile" },
];

export function BottomNav() {
	const pathname = usePathname();
	const [isVisible, setIsVisible] = useState(true);
	const [isExpanded, setIsExpanded] = useState(true);
	
	useEffect(() => {
		let hideTimeout: NodeJS.Timeout;
		let expandTimeout: NodeJS.Timeout;

		const resetTimers = () => {
			// Clear existing timeouts
			clearTimeout(hideTimeout);
			clearTimeout(expandTimeout);
			
			// Show navbar immediately
			setIsVisible(true);
			setIsExpanded(true);
			
			// Set timeout to hide after 6 seconds of inactivity
			hideTimeout = setTimeout(() => {
				setIsVisible(false);
			}, 6000);
			
			// Set timeout to collapse after 3 seconds of inactivity
			expandTimeout = setTimeout(() => {
				setIsExpanded(false);
			}, 3000);
		};

		// Reset timers on any user interaction
		const handleUserActivity = () => {
			resetTimers();
		};

		// Add event listeners for user activity
		document.addEventListener('mousedown', handleUserActivity);
		document.addEventListener('touchstart', handleUserActivity);
		document.addEventListener('keydown', handleUserActivity);
		document.addEventListener('scroll', handleUserActivity);

		// Initial timer setup
		resetTimers();

		// Cleanup
		return () => {
			clearTimeout(hideTimeout);
			clearTimeout(expandTimeout);
			document.removeEventListener('mousedown', handleUserActivity);
			document.removeEventListener('touchstart', handleUserActivity);
			document.removeEventListener('keydown', handleUserActivity);
			document.removeEventListener('scroll', handleUserActivity);
		};
	}, []);

	if (pathname?.startsWith("/login")) return null;

	const handleNavClick = (href: string, disabled?: boolean) => {
		if (disabled) {
			// Do nothing for disabled items
			return;
		}
		// Reset timers when navigation is used
		setIsVisible(true);
		setIsExpanded(true);
	};

	const handleExpandClick = () => {
		setIsVisible(true);
		setIsExpanded(true);
	};

	return (
		<>
			{/* Main Navigation Bar */}
			<div 
				className={`fixed bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 ease-in-out ${
					isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
				}`}
			>
				<nav className={`rounded-2xl border border-blue-100 bg-blue-50 shadow-md transition-all duration-300 ease-in-out ${
					isExpanded ? 'px-3 sm:px-5 py-2 sm:py-3' : 'px-2 py-2'
				} flex items-center gap-4 sm:gap-7 text-blue-700`}>
					{items.map(({ href, icon: Icon, label, disabled }) => {
						const active = pathname === href;
						const isDisabled = disabled || href === "#";
						
						if (isDisabled) {
							return (
								<div key={href} className="group cursor-default">
									<div className={`w-8 h-8 sm:w-10 sm:h-10 rounded-xl grid place-content-center transition opacity-50 ${active ? "bg-blue-200 text-blue-900" : "text-blue-700"}`}>
										<Icon size={16} className="sm:w-[18px] sm:h-[18px]" />
									</div>
									<span className="sr-only">{label}</span>
								</div>
							);
						}

						return (
							<Link key={href} href={href} className="group" onClick={() => handleNavClick(href)}>
								<div className={`w-8 h-8 sm:w-10 sm:h-10 rounded-xl grid place-content-center transition ${active ? "bg-blue-200 text-blue-900" : "text-blue-700 hover:bg-blue-100"}`}>
									<Icon size={16} className="sm:w-[18px] sm:h-[18px]" />
								</div>
								<span className="sr-only">{label}</span>
							</Link>
						);
					})}
				</nav>
			</div>

			{/* Expand Arrow (shown when navbar is hidden) */}
			<div 
				className={`fixed bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 transition-all duration-300 ease-in-out ${
					!isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 pointer-events-none'
				}`}
			>
				<button
					onClick={handleExpandClick}
					className="w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg transition-all duration-200 hover:scale-110 flex items-center justify-center"
				>
					<ChevronUp size={20} />
				</button>
			</div>
		</>
	);
}


