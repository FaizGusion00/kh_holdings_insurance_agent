"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../contexts/AuthContext";
import MedicalInsuranceRegistrationForm from "../(ui)/components/MedicalInsuranceRegistrationForm";

export default function RegisterClientPage() {
    const router = useRouter();
    const { user, isAuthenticated } = useAuth();
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        // Check if user is authenticated
        if (!isAuthenticated || !user) {
            router.push('/login?message=Please login to register clients');
            return;
        }
        setIsLoading(false);
    }, [isAuthenticated, user, router]);

    const handleRegistrationSuccess = (registration: any) => {
        // Redirect to dashboard after successful registration
        setTimeout(() => {
            router.push('/profile?tab=medical-insurance&message=Client registered successfully!');
        }, 2000);
    };

    const handleRegistrationClose = () => {
        // Redirect back to dashboard
        router.push('/profile');
    };

    if (isLoading) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600 mx-auto mb-4"></div>
                    <p className="text-gray-600">Loading registration form...</p>
                </div>
            </div>
        );
    }

    if (!user) {
        return null; // Will redirect to login
    }

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <div className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center py-4">
                        <div className="flex items-center">
                            <button
                                onClick={() => router.push('/profile')}
                                className="mr-4 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div className="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center mr-3">
                                <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-800">Register Client</h1>
                                <p className="text-sm text-gray-500">Agent: {user.name} ({user.agent_code})</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Registration Form */}
            <div className="flex items-center justify-center min-h-[calc(100vh-80px)] p-4">
                <MedicalInsuranceRegistrationForm
                    isOpen={true}
                    onClose={handleRegistrationClose}
                    onSuccess={handleRegistrationSuccess}
                    externalMode={false}
                    agentCode={user.agent_code}
                />
            </div>
        </div>
    );
}