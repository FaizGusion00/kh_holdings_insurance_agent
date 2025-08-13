"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Home, Compass, FileText, Hospital, UserRound } from "lucide-react";

const items = [
	{ href: "/dashboard", icon: Home, label: "Dashboard" },
	{ href: "/explore", icon: Compass, label: "Explore" },
	{ href: "/documents", icon: FileText, label: "Docs" },
	{ href: "/hospitals", icon: Hospital, label: "Hospitals" },
	{ href: "/profile", icon: UserRound, label: "Profile" },
];

export function BottomNav() {
	const pathname = usePathname();
	if (pathname?.startsWith("/login")) return null;

	return (
		<div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-50">
			<nav className="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-3 flex items-center gap-7 text-emerald-700 shadow-md">
				{items.map(({ href, icon: Icon, label }) => {
					const active = pathname === href;
					return (
						<Link key={href} href={href} className="group">
							<div className={`w-10 h-10 rounded-xl grid place-content-center transition ${active ? "bg-emerald-200 text-emerald-900" : "text-emerald-700 hover:bg-emerald-100"}`}>
								<Icon size={18} />
							</div>
							<span className="sr-only">{label}</span>
						</Link>
					);
				})}
			</nav>
		</div>
	);
}


