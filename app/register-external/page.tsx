"use client";

import { useState, useEffect } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import MedicalInsuranceRegistrationForm from "../(ui)/components/MedicalInsuranceRegistrationForm";

export default function ExternalRegistrationPage() {
    const searchParams = useSearchParams();
    const router = useRouter();
    const [agentCode, setAgentCode] = useState<string>("");
    const [isValidAgent, setIsValidAgent] = useState<boolean>(false);
    const [isLoading, setIsLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>("");

    useEffect(() => {
        const code = searchParams.get('agent_code');
        if (code) {
            setAgentCode(code);
            // For now, we'll assume any agent code is valid
            // In production, you might want to validate this against the database
            setIsValidAgent(true);
            setIsLoading(false);
        } else {
            setError("Invalid registration link. Agent code is missing.");
            setIsLoading(false);
        }
    }, [searchParams]);

    const handleRegistrationSuccess = (registration: any) => {
        // Redirect to login page after successful registration
        setTimeout(() => {
            router.push('/login?message=Registration successful! Please login to continue.');
        }, 2000);
    };

    const handleRegistrationClose = () => {
        // In external mode, we don't allow closing the modal
        // This function won't be called due to the external mode
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

    if (error || !isValidAgent) {
        return (
            <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
                    <div className="text-center">
                        <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg className="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <h2 className="text-xl font-bold text-gray-800 mb-2">Invalid Registration Link</h2>
                        <p className="text-gray-600 mb-6">{error || "The registration link is invalid or expired."}</p>
                        <button
                            onClick={() => router.push('/login')}
                            className="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                        >
                            Go to Login
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Header */}
            <div className="bg-white shadow-sm border-b">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center py-4">
                        <div className="flex items-center">
                            <div className="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center mr-3">
                                <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-gray-800">Medical Insurance Registration</h1>
                                <p className="text-sm text-gray-500">Agent Code: {agentCode}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Registration Form Modal */}
            <div className="flex items-center justify-center min-h-[calc(100vh-80px)] p-4">
                <MedicalInsuranceRegistrationForm
                    isOpen={true}
                    onClose={handleRegistrationClose}
                    onSuccess={handleRegistrationSuccess}
                    externalMode={true}
                    agentCode={agentCode}
                />
            </div>
        </div>
    );
}
