/**
 * Laravel API Service for KH Holdings Insurance
 * 
 * This service handles all API communications with the Laravel backend
 * including authentication, insurance plans, payments, MLM network, etc.
 */

interface ApiResponse<T = any> {
  status: 'success' | 'error';
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
}

interface User {
  id: number;
  name: string;
  email: string;
  phone_number: string;
  nric: string;
  agent_code?: string;
  referrer_code?: string;
  customer_type: 'client' | 'agent';
  status: 'active' | 'inactive' | 'suspended' | 'pending_verification';
  mlm_level: number;
  wallet_balance: number;
  total_commission_earned: number;
  registration_date: string;
}

interface InsurancePlan {
  id: number;
  plan_name: string;
  plan_code: string;
  description: string;
  monthly_price?: string;
  quarterly_price?: string;
  semi_annually_price?: string;
  annually_price?: string;
  commitment_fee: string;
  benefits: any;
  terms_conditions: any;
  min_age: number;
  max_age: number;
  is_active: boolean;
  pricing: {
    [key: string]: {
      base_price?: string;
      commitment_fee?: string;
      total_price?: string;
      price?: null;
    };
  };
  available_modes: string[];
}

interface PaymentTransaction {
  id: number;
  transaction_id: string;
  amount: string;
  currency: string;
  payment_method: string;
  status: 'pending' | 'completed' | 'failed' | 'cancelled';
  gateway_order_id?: string;
  gateway_payment_id?: string;
  paid_at?: string;
  created_at: string;
}

interface MemberPolicy {
  id: number;
  policy_number: string;
  insurance_plan: InsurancePlan;
  payment_mode: 'monthly' | 'quarterly' | 'semi_annually' | 'annually';
  premium_amount: string;
  policy_start_date: string;
  policy_end_date: string;
  status: 'active' | 'expired' | 'cancelled' | 'pending_payment';
  created_at: string;
}

interface DashboardStats {
  total_members: number;
  new_members: number;
  active_members: number;
  total_commission_earned: string;
  monthly_commission_earned: string;
  target_achievement: number;
  mlm_level: number;
  wallet_balance: string;
}

interface WalletData {
  balance: number;
  total_earned: number;
  recent_transactions: any[];
  withdrawal_requests: any[];
}

interface NetworkMember {
  id: number;
  name: string;
  email: string;
  agent_code: string;
  mlm_level: number;
  registration_date: string;
  status: string;
  active_policies_count: number;
  total_commission_earned: number;
  downline_count: number;
}

class LaravelApiService {
  private baseUrl: string;
  private token: string | null = null;

  constructor() {
    this.baseUrl = 'http://localhost:8000/api/v1';
    
    // Get token from localStorage if available
    if (typeof window !== 'undefined') {
      this.token = localStorage.getItem('auth_token');
    }
  }

  // Helper method to make authenticated requests
  private async makeRequest<T>(
    url: string,
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      (headers as any)['Authorization'] = `Bearer ${this.token}`;
    }

    const doFetch = async (): Promise<{ response: Response; data: any }> => {
      const response = await fetch(`${this.baseUrl}${url}`, {
        ...options,
        headers,
      });
      let data: any = null;
      try {
        data = await response.json();
      } catch (_) {
        // ignore JSON parse errors
      }
      return { response, data };
    };

    try {
      // First attempt
      let { response, data } = await doFetch();

      // If unauthorized, try refreshing token once and retry
      if (response.status === 401 && this.token) {
        const refresh = await this.refreshToken();
        if (refresh.status === 'success') {
          // Rebuild headers with new token
          if (this.token) {
            (headers as any)['Authorization'] = `Bearer ${this.token}`;
          }
          ({ response, data } = await doFetch());
        }
      }

      if (!response.ok) {
        // For validation/auth errors, bubble backend payload to caller instead of throwing
        if (data && (response.status === 400 || response.status === 401 || response.status === 422)) {
          return data as ApiResponse<T>;
        }
        throw new Error((data && data.message) || `HTTP error! status: ${response.status}`);
      }

      return data as ApiResponse<T>;
    } catch (error) {
      console.error('API request failed:', error);
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'An error occurred',
      };
    }
  }

  // =====================
  // AUTHENTICATION APIs
  // =====================

  async register(userData: {
    name: string;
    email: string;
    phone_number: string;
    nric: string;
    race: string;
    date_of_birth: string;
    gender: string;
    occupation: string;
    emergency_contact_name: string;
    emergency_contact_phone: string;
    emergency_contact_relationship: string;
    address: string;
    city: string;
    state: string;
    postal_code: string;
    password: string;
    password_confirmation: string;
    referrer_code?: string;
  }): Promise<ApiResponse<{ user: User; access_token: string; token_type: string; expires_in: number }>> {
    const response = await this.makeRequest<{ user: User; access_token: string; token_type: string; expires_in: number }>(
      '/auth/register',
      {
        method: 'POST',
        body: JSON.stringify(userData),
      }
    );

    if (response.status === 'success' && response.data?.access_token) {
      this.setToken(response.data.access_token);
    }

    return response;
  }

  async login(identifier: string, password: string): Promise<ApiResponse<{ user: User; access_token: string; token_type: string; expires_in: number }>> {
    const isAgentCode = /^AGT\d{5}$/i.test(identifier.trim());
    const payload: any = isAgentCode ? { agent_code: identifier.trim().toUpperCase(), password } : { email: identifier.trim(), password };
    const response = await this.makeRequest<{ user: User; access_token: string; token_type: string; expires_in: number }>(
      '/auth/login',
      {
        method: 'POST',
        body: JSON.stringify(payload),
      }
    );

    if (response.status === 'success' && response.data?.access_token) {
      this.setToken(response.data.access_token);
    }

    return response;
  }

  async logout(): Promise<ApiResponse> {
    const response = await this.makeRequest('/auth/logout', { method: 'POST' });
    this.clearToken();
    return response;
  }

  async updateProfile(profileData: any): Promise<ApiResponse<{ user: User }>> {
    return this.makeRequest<{ user: User }>('/auth/profile', {
      method: 'PUT',
      body: JSON.stringify(profileData),
    });
  }

  async changePassword(passwordData: { current_password: string; new_password: string; new_password_confirmation: string }): Promise<ApiResponse> {
    return this.makeRequest('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify(passwordData),
    });
  }

  async getMe(): Promise<ApiResponse<{ user: User }>> {
    return this.makeRequest<{ user: User }>('/auth/me');
  }

  async refreshToken(): Promise<ApiResponse<{ authorisation: { token: string; type: string } }>> {
    const response = await this.makeRequest<{ authorisation: { token: string; type: string } }>(
      '/auth/refresh',
      { method: 'POST' }
    );
    if (response.status === 'success' && response.data?.authorisation?.token) {
      this.setToken(response.data.authorisation.token);
    }

    return response;
  }

  // =====================
  // INSURANCE PLAN APIs
  // =====================

  async getInsurancePlans(): Promise<ApiResponse<{ plans: InsurancePlan[]; total: number }>> {
    return this.makeRequest<{ plans: InsurancePlan[]; total: number }>('/plans');
  }

  async getInsurancePlan(id: number): Promise<ApiResponse<{ plan: InsurancePlan }>> {
    return this.makeRequest<{ plan: InsurancePlan }>(`/plans/${id}`);
  }

  async getCommissionRates(planId: number): Promise<ApiResponse<any>> {
    return this.makeRequest(`/plans/${planId}/commission-rates`);
  }

  // =====================
  // DASHBOARD APIs
  // =====================

  async getDashboardStats(): Promise<ApiResponse<{ stats: DashboardStats; recent_activities: any[]; performance_data: any }>> {
    return this.makeRequest<{ stats: DashboardStats; recent_activities: any[]; performance_data: any }>('/dashboard');
  }

  async getMembers(params?: { per_page?: number; search?: string }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/dashboard/members${queryString}`);
  }

  // =====================
  // WALLET APIs
  // =====================

  async getAgentWallet(): Promise<ApiResponse<WalletData>> {
    return this.makeRequest<WalletData>('/wallet');
  }

  async getWalletBalance(): Promise<ApiResponse<{ balance: number; total_earned: number }>> {
    return this.makeRequest<{ balance: number; total_earned: number }>('/wallet/balance');
  }

  async getWalletTransactions(page: number = 1, type?: string): Promise<ApiResponse<any>> {
    const params = new URLSearchParams({ page: page.toString() });
    if (type) params.append('type', type);
    
    return this.makeRequest(`/wallet/transactions?${params.toString()}`);
  }

  async createWithdrawalRequest(data: {
    amount: number;
    bank_name: string;
    bank_account_number: string;
    bank_account_owner: string;
  }): Promise<ApiResponse<any>> {
    return this.makeRequest('/wallet/withdraw', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async getWithdrawalRequests(): Promise<ApiResponse<any>> {
    return this.makeRequest('/wallet/withdrawals');
  }

  // =====================
  // MLM NETWORK APIs
  // =====================

  async getNetwork(params?: { per_page?: number }): Promise<ApiResponse<{ network_members: any }>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest<{ network_members: any }>(`/mlm/network${queryString}`);
  }

  async getCommissionHistory(params?: { per_page?: number; from?: string; to?: string }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/mlm/commission-history${queryString}`);
  }

  async getLevelSummary(): Promise<ApiResponse<any>> {
    return this.makeRequest('/mlm/level-summary');
  }

  async registerClient(clientData: any): Promise<ApiResponse<{ client: User }>> {
    return this.makeRequest<{ client: User }>('/mlm/register-client', {
      method: 'POST',
      body: JSON.stringify(clientData),
    });
  }

  async getTeamPerformance(): Promise<ApiResponse<any>> {
    return this.makeRequest('/mlm/team-performance');
  }

  // =====================
  // POLICY APIs
  // =====================

  async getPolicies(params?: { per_page?: number }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/policies${queryString}`);
  }

  async getPolicy(id: number): Promise<ApiResponse<{ policy: MemberPolicy }>> {
    return this.makeRequest<{ policy: MemberPolicy }>(`/policies/${id}`);
  }

  async purchasePolicy(data: {
    insurance_plan_id: number;
    payment_mode: 'monthly' | 'quarterly' | 'semi_annually' | 'annually';
  }): Promise<ApiResponse<{ policy: MemberPolicy }>> {
    return this.makeRequest<{ policy: MemberPolicy }>('/policies/purchase', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // =====================
  // PAYMENT APIs
  // =====================

  async getPayments(params?: { per_page?: number }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/payments${queryString}`);
  }

  async createPayment(data: {
    member_policy_id: number;
    payment_method: string;
    return_url?: string;
    cancel_url?: string;
  }): Promise<ApiResponse<{
    payment: PaymentTransaction;
    checkout_data: any;
    curlec_options: any;
  }>> {
    return this.makeRequest<{
      payment: PaymentTransaction;
      checkout_data: any;
      curlec_options: any;
    }>('/payments/create', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async verifyPayment(data: any): Promise<ApiResponse> {
    return this.makeRequest('/payments/callback', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async getReceipt(paymentId: number): Promise<ApiResponse<{ receipt: PaymentTransaction }>> {
    return this.makeRequest<{ receipt: PaymentTransaction }>(`/payments/receipts/${paymentId}`);
  }

  // =====================
  // HOSPITAL & CLINIC APIs
  // =====================

  async getHospitals(params?: { per_page?: number; search?: string; state?: string }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/hospitals${queryString}`);
  }

  async getClinics(params?: { per_page?: number; search?: string; state?: string }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/clinics${queryString}`);
  }

  async searchHospitals(query: string): Promise<ApiResponse<{ hospitals: any[] }>> {
    return this.makeRequest<{ hospitals: any[] }>(`/hospitals/search?q=${encodeURIComponent(query)}`);
  }

  async searchClinics(query: string): Promise<ApiResponse<{ clinics: any[] }>> {
    return this.makeRequest<{ clinics: any[] }>(`/clinics/search?q=${encodeURIComponent(query)}`);
  }

  // =====================
  // NOTIFICATION APIs
  // =====================

  async getNotifications(params?: { per_page?: number; type?: string }): Promise<ApiResponse<any>> {
    const queryString = params ? '?' + new URLSearchParams(params as any).toString() : '';
    return this.makeRequest(`/notifications${queryString}`);
  }

  async markNotificationAsRead(id: number): Promise<ApiResponse> {
    return this.makeRequest(`/notifications/${id}/read`, { method: 'PUT' });
  }

  async markAllNotificationsAsRead(): Promise<ApiResponse> {
    return this.makeRequest('/notifications/mark-all-read', { method: 'PUT' });
  }

  async getUnreadNotificationsCount(): Promise<ApiResponse<{ unread_count: number }>> {
    return this.makeRequest<{ unread_count: number }>('/notifications/unread-count');
  }

  // =====================
  // TOKEN MANAGEMENT
  // =====================

  private setToken(token: string): void {
    this.token = token;
    if (typeof window !== 'undefined') {
      localStorage.setItem('auth_token', token);
    }
  }

  private clearToken(): void {
    this.token = null;
    if (typeof window !== 'undefined') {
      localStorage.removeItem('auth_token');
    }
  }

  getToken(): string | null {
    return this.token;
  }

  isAuthenticated(): boolean {
    return !!this.token;
  }

  // =====================
  // CURLEC PAYMENT INTEGRATION
  // =====================

  /**
   * Initialize Curlec payment for a policy
   */
  async initiateCurlecPayment(
    policyId: number,
    paymentMethod: string = 'curlec',
    returnUrl?: string,
    cancelUrl?: string
  ): Promise<void> {
    try {
      const response = await this.createPayment({
        member_policy_id: policyId,
        payment_method: paymentMethod,
        return_url: returnUrl,
        cancel_url: cancelUrl,
      });

      if (response.status === 'success' && response.data) {
        this.openCurlecCheckout(response.data.checkout_data);
      } else {
        throw new Error(response.message || 'Failed to create payment');
      }
    } catch (error) {
      console.error('Payment initiation failed:', error);
      throw error;
    }
  }

  /**
   * Open Curlec checkout popup
   */
  private openCurlecCheckout(checkoutData: any): void {
    if (typeof window === 'undefined' || !(window as any).Razorpay) {
      throw new Error('Curlec/Razorpay SDK not loaded');
    }

    const options = {
      ...checkoutData,
      handler: (response: any) => {
        this.handlePaymentSuccess(response);
      },
      modal: {
        ondismiss: () => {
          console.log('Payment cancelled by user');
          this.handlePaymentCancellation();
        }
      }
    };

    const rzp = new (window as any).Razorpay(options);
    rzp.open();
  }

  /**
   * Handle successful payment
   */
  private async handlePaymentSuccess(response: any): Promise<void> {
    try {
      await this.verifyPayment(response);
      
      // Redirect to success page or update UI
      if (typeof window !== 'undefined') {
        window.location.href = '/payment-success';
      }
    } catch (error) {
      console.error('Payment verification failed:', error);
      // Handle verification failure
    }
  }

  /**
   * Handle payment cancellation
   */
  private handlePaymentCancellation(): void {
    // Handle payment cancellation
    console.log('Payment was cancelled');
    // You can show a message or redirect as needed
  }

  // Bulk Client Registration
  async registerBulkClients(clientsData: {
    clients: Array<{
      name: string;
      email: string;
      phone_number: string;
      nric: string;
      date_of_birth: string;
      gender: 'Male' | 'Female';
      password: string;
      insurance_plan_id: number;
      payment_mode: 'monthly' | 'quarterly' | 'semi_annually' | 'annually';
      medical_card_type: string;
      race?: string;
      occupation?: string;
      address?: string;
      city?: string;
      state?: string;
      postal_code?: string;
      emergency_contact_name?: string;
      emergency_contact_phone?: string;
      emergency_contact_relationship?: string;
      medical_consultation_2_years?: boolean;
      serious_illness_history?: boolean;
      insurance_rejection_history?: boolean;
      serious_injury_history?: boolean;
    }>
  }): Promise<ApiResponse<{
    registration_id: number;
    clients: User[];
    policies: any[];
    total_amount: number;
    payment_transaction: any;
  }>> {
    return this.makeRequest('/mlm/register-bulk-clients', {
      method: 'POST',
      body: JSON.stringify(clientsData),
    });
  }

  // Create Bulk Payment
  async createBulkPayment(paymentData: {
    registration_id: number;
    payment_method: string;
    return_url?: string;
    cancel_url?: string;
  }): Promise<ApiResponse<{
    payment: any;
    checkout_data: any;
    curlec_options: any;
  }>> {
    return this.makeRequest('/payments/create-bulk', {
      method: 'POST',
      body: JSON.stringify(paymentData),
    });
  }

  // (deduplicated) Get Insurance Plans is defined above returning { plans, total }

  // Verify Bulk Payment
  async verifyBulkPayment(verificationData: {
    razorpay_order_id: string;
    razorpay_payment_id: string;
    razorpay_signature: string;
    registration_id: number;
  }): Promise<ApiResponse<any>> {
    return this.makeRequest('/payments/verify', {
      method: 'POST',
      body: JSON.stringify(verificationData),
    });
  }
}

// Export singleton instance
export const laravelApi = new LaravelApiService();

// Export types for use in components
export type {
  ApiResponse,
  User,
  InsurancePlan,
  PaymentTransaction,
  MemberPolicy,
  DashboardStats,
  WalletData,
  NetworkMember,
};
