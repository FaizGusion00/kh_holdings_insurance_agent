"use client";

import { X } from "lucide-react";
import { ReactNode, useEffect } from "react";

export function Modal({ open, onClose, title, children, maxWidth = "max-w-3xl" }: { open: boolean; onClose: () => void; title?: string; children: ReactNode; maxWidth?: string }) {
	useEffect(() => {
		if (!open) return;
		const onKey = (e: KeyboardEvent) => { if (e.key === "Escape") onClose(); };
		document.addEventListener("keydown", onKey);
		return () => document.removeEventListener("keydown", onKey);
	}, [open, onClose]);

	if (!open) return null;

	return (
		<div className="fixed inset-0 z-[60] flex items-center justify-center p-3">
			<div className="absolute inset-0 bg-black/30" onClick={onClose} />
			<div className={`relative w-full ${maxWidth} bg-white rounded-2xl shadow-xl border border-blue-100 overflow-hidden`}>
				<div className="flex items-center justify-between px-4 sm:px-5 py-3 border-b">
					<div className="font-semibold text-gray-800 text-base sm:text-lg">{title}</div>
					<button onClick={onClose} className="w-8 h-8 rounded-full hover:bg-gray-100 grid place-content-center text-gray-500"><X size={18} /></button>
				</div>
				<div className="p-4 sm:p-5 max-h-[80vh] overflow-auto">
					{children}
				</div>
			</div>
		</div>
	);
}


