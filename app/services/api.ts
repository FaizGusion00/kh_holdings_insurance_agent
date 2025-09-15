import { env } from '../../lib/env';

const API_BASE_URL = env.API_URL;

// Types
export interface User {
  id: number;
  name: string;
  email: string;
  agent_code: string; // format: AGT00001
  phone_number: string;
  nric: string;
  address: string;
  city: string;
  state: string;
  postal_code: string;
  bank_name: string;
  bank_account_number: string;
  bank_account_owner: string;
  mlm_level: number;
  total_commission_earned: string;
  monthly_commission_target: string;
  status: string;
  phone_verified_at: string;
  mlm_activation_date: string;
}

export interface Member {
  id: number;
  user_id: number;
  name: string;
  nric: string;
  phone: string;
  email: string;
  address: string;
  date_of_birth: string;
  gender: string;
  occupation: string;
  race: string;
  relationship_with_agent: string;
  status: string;
  registration_date: string;
  emergency_contact_name: string;
  emergency_contact_phone: string;
  emergency_contact_relationship: string;
  balance: number;
  referrer_code: string;
  referrer_id: number;
  created_at: string;
  updated_at: string;
}

export interface Policy {
  id: number;
  member_id: number;
  product_id: number;
  policy_number: string;
  start_date: string;
  end_date: string;
  premium_amount: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface PaymentTransaction {
  id: number;
  member_id: number;
  policy_id: number | null;
  amount: string;
  payment_type: string;
  payment_method: string;
  status: string;
  transaction_date: string;
  description: string;
  reference_number: string;
  member?: Member;
  policy?: Policy;
}

export interface PaymentMandate {
  id: number;
  user_id: number;
  member_id: number;
  policy_id: number | null;
  mandate_type: string;
  frequency: string;
  amount: string;
  start_date: string;
  end_date: string | null;
  bank_account: string;
  bank_name: string;
  status: string;
  reference_number: string;
  next_processing_date: string;
  total_processed: number;
  total_amount_processed: string;
  member?: Member;
  policy?: Policy;
}

export interface Notification {
  id: number;
  user_id: number;
  type: string;
  title: string;
  message: string;
  data?: any;
  action_url?: string;
  action_text?: string;
  is_read: boolean;
  is_important: boolean;
  read_at?: string;
  created_at: string;
  time_ago: string;
  icon: string;
  color: string;
  background_color: string;
}

export interface DashboardStats {
  total_members: number;
  active_members: number;
  new_members: number;
  total_commission_earned: string;
  monthly_commission: string;
  commission_target: string;
  target_achievement: number;
  total_referrals: number;
  direct_referrals: number;
  mlm_level: number;
}

export interface RecentActivity {
  id: number;
  type: string;
  description: string;
  created_at: string;
  user_id: number;
  member_id?: number;
}

export interface PerformanceData {
  monthly_target: number;
  current_month: number;
  achievement_percentage: number;
  trend: 'up' | 'down' | 'stable';
}

export interface PaymentStats {
  total_payments: number;
  total_amount: string;
  pending_payments: number;
  failed_payments: number;
  success_rate: number;
}

export interface HealthcareFacility {
  id: number;
  name: string;
  type: string;
  address: string;
  city: string;
  state: string;
  postal_code: string;
  phone: string;
  email: string;
  website: string;
  operating_hours: string;
  services: string[];
  insurance_accepted: string[];
  rating: number;
  distance: number;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface SharingMonthlyPerformanceItem {
  month: number;
  month_name: string;
  commission: number;
  new_members: number;
  payments: number;
  hospital_cases?: number;
  clinic_cases?: number;
}

export interface RecordsPerformanceData {
  // shape not fully defined yet; keep as unknown-safe structure
  [key: string]: unknown;
}

export interface RecordsSharingResponse {
  monthly_performance: SharingMonthlyPerformanceItem[];
  current_month: number;
  current_year: number;
}

// API Service Class
class ApiService {
  private token: string | null = null;

  constructor() {
    // Get token from localStorage on initialization
    if (typeof window !== 'undefined') {
      this.token = localStorage.getItem('auth_token');
    }
  }

  private getHeaders(): HeadersInit {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    return headers;
  }

  // Enhanced response handler with better error categorization
  private async handleResponse<T>(response: Response): Promise<ApiResponse<T>> {
    try {
      const data = await response.json();
      
      if (response.ok) {
        return {
          success: true,
          data: data.data || data,
          message: data.message
        };
      }
      
      // Handle different HTTP error statuses
      switch (response.status) {
        case 400:
          return {
            success: false,
            message: 'Invalid request. Please check your input.',
            errors: data.errors || { general: [data.message || 'Bad request'] }
          };
        case 401:
          // Clear token on authentication failure
          this.clearToken();
          return {
            success: false,
            message: 'Authentication failed. Please login again.',
            errors: { authentication: ['Invalid credentials or expired session'] }
          };
        case 403:
          return {
            success: false,
            message: 'Access denied. You do not have permission to perform this action.',
            errors: { authorization: ['Insufficient permissions'] }
          };
        case 404:
          return {
            success: false,
            message: 'Resource not found.',
            errors: { general: ['The requested resource was not found'] }
          };
        case 422:
          return {
            success: false,
            message: 'Validation failed. Please check your input.',
            errors: data.errors || { validation: [data.message || 'Validation error'] }
          };
        case 429:
          return {
            success: false,
            message: 'Too many requests. Please wait before trying again.',
            errors: { rate_limit: ['Rate limit exceeded'] }
          };
        case 500:
          return {
            success: false,
            message: 'Server error. Please try again later.',
            errors: { server: ['Internal server error'] }
          };
        default:
          return {
            success: false,
            message: data.message || `Request failed with status ${response.status}`,
            errors: { general: [data.message || 'Unknown error occurred'] }
          };
      }
    } catch (error) {
      console.error('Error parsing response:', error);
      return {
        success: false,
        message: 'Failed to parse server response.',
        errors: { general: ['Response parsing error'] }
      };
    }
  }

  // Enhanced fetch wrapper with timeout
  private async fetchWithTimeout(url: string, options: RequestInit, timeout = 30000): Promise<Response> {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);
    
    try {
      const response = await fetch(url, {
        ...options,
        signal: controller.signal,
      });
      clearTimeout(timeoutId);
      return response;
    } catch (error) {
      clearTimeout(timeoutId);
      throw error;
    }
  }

  setToken(token: string) {
    this.token = token;
    if (typeof window !== 'undefined') {
      localStorage.setItem('auth_token', token);
    }
  }

  clearToken() {
    this.token = null;
    if (typeof window !== 'undefined') {
      localStorage.removeItem('auth_token');
    }
  }

  isAuthenticated(): boolean {
    return !!this.token;
  }

  // Authentication
  async login(agentCode: string, password: string): Promise<ApiResponse<{ user: User; token: string; token_type: string }>> {
    const response = await fetch(`${API_BASE_URL}/auth/login`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify({
        agent_code: agentCode,
        password: password
      })
    });

    const result = await this.handleResponse<{ user: User; token: string; token_type: string }>(response);
    if (result.success && result.data?.token) {
      this.setToken(result.data.token);
    }
    return result;
  }

  async logout(): Promise<ApiResponse<{ message?: string }>> {
    const response = await fetch(`${API_BASE_URL}/auth/logout`, {
      method: 'POST',
      headers: this.getHeaders()
    });

    const result = await this.handleResponse<{ message?: string }>(response);
    this.clearToken();
    return result;
  }

  async getProfile(): Promise<ApiResponse<{ user: User; profile_complete: number }>> {
    const response = await fetch(`${API_BASE_URL}/profile`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ user: User; profile_complete: number }>(response);
  }

  // Dashboard
  async getDashboardStats(): Promise<ApiResponse<{ stats: DashboardStats; recent_activities: RecentActivity[]; performance_data: PerformanceData }>> {
    const response = await fetch(`${API_BASE_URL}/dashboard`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ stats: DashboardStats; recent_activities: RecentActivity[]; performance_data: PerformanceData }>(response);
  }

  // Members
  async getMembers(page: number = 1, search?: string, status?: string): Promise<ApiResponse<{ data: Member[]; current_page: number; total: number; per_page: number }>> {
    const params = new URLSearchParams({
      page: page.toString(),
      ...(search && { search }),
      ...(status && { status })
    });

    const response = await fetch(`${API_BASE_URL}/members?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ data: Member[]; current_page: number; total: number; per_page: number }>(response);
  }

  async createMember(memberData: Partial<Member>): Promise<ApiResponse<Member>> {
    const response = await fetch(`${API_BASE_URL}/members`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(memberData)
    });

    return await this.handleResponse<Member>(response);
  }

  async updateMember(id: number, memberData: Partial<Member>): Promise<ApiResponse<Member>> {
    const response = await fetch(`${API_BASE_URL}/members/${id}`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(memberData)
    });

    return await this.handleResponse<Member>(response);
  }

  async deleteMember(id: number): Promise<ApiResponse<{ message?: string }>> {
    const response = await fetch(`${API_BASE_URL}/members/${id}`, {
      method: 'DELETE',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ message?: string }>(response);
  }

  // Payments
  async getPayments(): Promise<ApiResponse<{ stats: PaymentStats; recent_payments: PaymentTransaction[]; active_mandates: PaymentMandate[] }>> {
    const response = await fetch(`${API_BASE_URL}/payments`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ stats: PaymentStats; recent_payments: PaymentTransaction[]; active_mandates: PaymentMandate[] }>(response);
  }

  async getPaymentHistory(page: number = 1, month?: number, year?: number, status?: string, type?: string): Promise<ApiResponse<{ data: PaymentTransaction[]; current_page: number; total: number }>> {
    const params = new URLSearchParams({
      page: page.toString(),
      ...(month && { month: month.toString() }),
      ...(year && { year: year.toString() }),
      ...(status && { status }),
      ...(type && { type })
    });

    const response = await fetch(`${API_BASE_URL}/payments/history?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ data: PaymentTransaction[]; current_page: number; total: number }>(response);
  }

  async getPaymentMandates(): Promise<ApiResponse<PaymentMandate[]>> {
    const response = await fetch(`${API_BASE_URL}/payments/mandates`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<PaymentMandate[]>(response);
  }

  async processPayment(paymentData: {
    member_id: number;
    policy_id?: number;
    amount: number;
    payment_type: string;
    payment_method: string;
    description?: string;
  }): Promise<ApiResponse<PaymentTransaction>> {
    const response = await fetch(`${API_BASE_URL}/payments/process`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(paymentData)
    });

    return await this.handleResponse<PaymentTransaction>(response);
  }

  async setupPaymentMandate(mandateData: {
    member_id: number;
    policy_id?: number;
    mandate_type: string;
    frequency: string;
    amount: number;
    start_date: string;
    end_date?: string;
    bank_account: string;
    bank_name: string;
  }): Promise<ApiResponse<PaymentMandate>> {
    const response = await fetch(`${API_BASE_URL}/payments/setup-mandate`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(mandateData)
    });

    return await this.handleResponse<PaymentMandate>(response);
  }

  // Profile Management
  async updateProfile(profileData: {
    name?: string;
    current_password?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
  }): Promise<ApiResponse<User>> {
    const response = await fetch(`${API_BASE_URL}/profile`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(profileData)
    });

    return await this.handleResponse<User>(response);
  }

  async changePassword(passwordData: {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
  }): Promise<ApiResponse<{ message?: string }>> {
    const response = await fetch(`${API_BASE_URL}/profile/change-password`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(passwordData)
    });

    return await this.handleResponse<{ message?: string }>(response);
  }

  async changePhone(phoneData: {
    current_phone: string;
    new_phone: string;
    tac_code: string;
  }): Promise<ApiResponse<{ message?: string }>> {
    const response = await fetch(`${API_BASE_URL}/profile/change-phone`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(phoneData)
    });

    return await this.handleResponse<{ message?: string }>(response);
  }


  // TAC (Transaction Authorization Code)
  async sendTac(phoneNumber: string, purpose: string): Promise<ApiResponse<{ sent: boolean; expires_in?: number }>> {
    const response = await fetch(`${API_BASE_URL}/tac/send`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify({
        phone_number: phoneNumber,
        purpose: purpose
      })
    });

    return await this.handleResponse<{ sent: boolean; expires_in?: number }>(response);
  }

  async verifyTac(phoneNumber: string, tacCode: string, purpose: string): Promise<ApiResponse<{ verified: boolean }>> {
    const response = await fetch(`${API_BASE_URL}/tac/verify`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify({
        phone_number: phoneNumber,
        tac_code: tacCode,
        purpose: purpose
      })
    });

    return await this.handleResponse<{ verified: boolean }>(response);
  }

  // Healthcare Facilities
  async getHealthcareFacilities(): Promise<ApiResponse<HealthcareFacility[]>> {
    const response = await fetch(`${API_BASE_URL}/healthcare`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<HealthcareFacility[]>(response);
  }

  async getHospitals(): Promise<ApiResponse<HealthcareFacility[]>> {
    const response = await fetch(`${API_BASE_URL}/healthcare/hospitals`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<HealthcareFacility[]>(response);
  }

  async getClinics(): Promise<ApiResponse<HealthcareFacility[]>> {
    const response = await fetch(`${API_BASE_URL}/healthcare/clinics`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<HealthcareFacility[]>(response);
  }

  async searchHealthcareFacilities(query: string): Promise<ApiResponse<HealthcareFacility[]>> {
    const response = await fetch(`${API_BASE_URL}/healthcare/search?q=${encodeURIComponent(query)}`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<HealthcareFacility[]>(response);
  }

  // Records
  async getPerformanceData(): Promise<ApiResponse<RecordsPerformanceData>> {
    const response = await fetch(`${API_BASE_URL}/records/performance`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<RecordsPerformanceData>(response);
  }

  async getSharingRecords(): Promise<ApiResponse<RecordsSharingResponse>> {
    const response = await fetch(`${API_BASE_URL}/records/sharing`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<RecordsSharingResponse>(response);
  }

  // Medical Insurance
  async getMedicalInsurancePlans(): Promise<ApiResponse<any[]>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/plans`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    return await this.handleResponse<any[]>(response);
  }

  async getMedicalInsurancePlan(id: number): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/plans/${id}`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<any>(response);
  }

  async registerMedicalInsurance(registrationData: any): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/register`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(registrationData)
    });

    return await this.handleResponse<any>(response);
  }

  async registerMedicalInsuranceExternal(registrationData: any): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/register-external`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(registrationData)
    });

    return await this.handleResponse<any>(response);
  }

  async getMedicalInsuranceRegistrations(): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/registrations`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<any>(response);
  }

  async getMedicalInsuranceRegistrationStatus(id: number): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/registrations/${id}`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<any>(response);
  }

  async createMedicalInsurancePaymentOrder(paymentData: any): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/payment/create-order`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(paymentData)
    });

    return await this.handleResponse<any>(response);
  }

  async createMedicalInsurancePaymentOrderForAllCustomers(paymentData: any): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/payment/create-order-all`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(paymentData)
    });

    return await this.handleResponse<any>(response);
  }

  async verifyMedicalInsurancePayment(verificationData: any): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/payment/verify`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(verificationData)
    });

    return await this.handleResponse<any>(response);
  }

  async getMedicalInsurancePaymentConfig(): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/payment/config`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<any>(response);
  }

  async getGatewayPaymentHistory(page: number = 1): Promise<ApiResponse<{ data: any[]; current_page: number; total: number }>> {
    const params = new URLSearchParams({ page: String(page) });
    const response = await fetch(`${API_BASE_URL}/medical-insurance/payment/gateway-history?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{ data: any[]; current_page: number; total: number }>(response);
  }

  // Clients (agent's customers)
  async getClients(page: number = 1): Promise<ApiResponse<{ data: any[]; current_page: number; total: number }>> {
    const params = new URLSearchParams({ page: String(page) });
    const response = await fetch(`${API_BASE_URL}/clients?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{ data: any[]; current_page: number; total: number }>(response);
  }

  async getClient(id: number): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/clients/${id}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any>(response);
  }

  async getClientPayments(id: number): Promise<ApiResponse<any[]>> {
    const response = await fetch(`${API_BASE_URL}/clients/${id}/payments`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any[]>(response);
  }

  async downloadClientCard(id: number): Promise<Blob> {
    const response = await fetch(`${API_BASE_URL}/clients/${id}/card`, {
      method: 'GET',
      headers: {
        ...(this.getHeaders()),
        'Accept': 'image/svg+xml'
      }
    });
    return await response.blob();
  }

  async getMedicalInsurancePolicies(): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/policies`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<any>(response);
  }

  // Plans Methods
  async getPlans(): Promise<ApiResponse<any[]>> {
    const response = await fetch(`${API_BASE_URL}/plans`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any[]>(response);
  }

  async getPlan(id: number): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/plans/${id}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any>(response);
  }

  async getPlanPricing(id: number): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/plans/${id}/pricing`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any>(response);
  }

  // Agent Wallet Methods
  async getAgentWallet(): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/agent/wallet`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any>(response);
  }

  async requestWithdrawal(data: { amount: number; notes?: string }): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/agent/wallet/withdraw`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    });
    return await this.handleResponse<any>(response);
  }

  async getWithdrawalHistory(page: number = 1): Promise<ApiResponse<{ data: any[]; current_page: number; total: number }>> {
    const params = new URLSearchParams({ page: String(page) });
    const response = await fetch(`${API_BASE_URL}/agent/wallet/withdrawals?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{ data: any[]; current_page: number; total: number }>(response);
  }

  async getWalletTransactions(page: number = 1): Promise<ApiResponse<{ data: any[]; current_page: number; total: number }>> {
    const params = new URLSearchParams({ page: String(page) });
    const response = await fetch(`${API_BASE_URL}/agent/wallet/transactions?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{ data: any[]; current_page: number; total: number }>(response);
  }

  // Referral methods
  async getReferrals(): Promise<ApiResponse<{
    referral: any;
    direct_referrals: any[];
    upline_agents: any[];
    downline_stats: any;
  }>> {
    const response = await fetch(`${API_BASE_URL}/referrals`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{
      referral: any;
      direct_referrals: any[];
      upline_agents: any[];
      downline_stats: any;
    }>(response);
  }

  async getReferralTree(): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/referrals/tree`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any>(response);
  }

  async getDownlines(level?: number): Promise<ApiResponse<{
    data: any[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  }>> {
    const params = new URLSearchParams();
    if (level) params.append('level', level.toString());
    
    const response = await fetch(`${API_BASE_URL}/referrals/downlines?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{
      data: any[];
      current_page: number;
      last_page: number;
      total: number;
      per_page: number;
    }>(response);
  }

  async getUserDownlines(userId: number, level?: number, page: number = 1): Promise<ApiResponse<{
    data: any[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    by_level_counts?: Record<string, number>;
  }>> {
    const params = new URLSearchParams({ page: String(page) });
    if (level) params.set('level', String(level));
    const response = await fetch(`${API_BASE_URL}/referrals/${userId}/downlines?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any>(response);
  }

  async getUplines(): Promise<ApiResponse<any[]>> {
    const response = await fetch(`${API_BASE_URL}/referrals/uplines`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<any[]>(response);
  }

  // Commission methods
  async getMyCommissions(): Promise<ApiResponse<{
    data: any[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  }>> {
    const response = await fetch(`${API_BASE_URL}/commissions/my-commissions`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{
      data: any[];
      current_page: number;
      last_page: number;
      total: number;
      per_page: number;
    }>(response);
  }

  async getCommissionSummary(): Promise<ApiResponse<{
    total_commission: number;
    monthly_commission: number;
    pending_commission: number;
    paid_commission: number;
    performance_metrics: any;
  }>> {
    const response = await fetch(`${API_BASE_URL}/commissions/summary`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{
      total_commission: number;
      monthly_commission: number;
      pending_commission: number;
      paid_commission: number;
      performance_metrics: any;
    }>(response);
  }

  async getCommissionHistory(): Promise<ApiResponse<{
    data: any[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  }>> {
    const response = await fetch(`${API_BASE_URL}/commissions/history`, {
      method: 'GET',
      headers: this.getHeaders()
    });
    return await this.handleResponse<{
      data: any[];
      current_page: number;
      last_page: number;
      total: number;
      per_page: number;
    }>(response);
  }

  // Bank info update
  async updateBankInfo(data: {
    bank_name: string;
    bank_account_number: string;
    bank_account_owner: string;
    current_password: string;
  }): Promise<ApiResponse<any>> {
    const response = await fetch(`${API_BASE_URL}/profile/bank-info`, {
      method: 'PUT',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    });
    return await this.handleResponse<any>(response);
  }

  // Notifications
  async getNotifications(limit: number = 100, unreadOnly: boolean = false): Promise<ApiResponse<{
    data: Notification[];
    unread_count: number;
  }>> {
    const params = new URLSearchParams({
      limit: limit.toString(),
      unread_only: unreadOnly.toString()
    });

    // Backend routes are namespaced under /medical-insurance
    const response = await this.fetchWithTimeout(`${API_BASE_URL}/medical-insurance/notifications?${params}`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    // Normalize shape because backend returns { data: Notification[], unread_count }
    // while the generic handler expects nested objects in some endpoints.
    try {
      const raw = await response.json();
      if (!response.ok) {
        // Fall back to generic error handling for non-2xx
        return {
          success: false,
          message: raw?.message || `Request failed with status ${response.status}`,
          errors: raw?.errors || { general: [raw?.message || 'Unknown error occurred'] }
        };
      }

      const notifications: Notification[] = Array.isArray(raw?.data)
        ? raw.data
        : Array.isArray(raw?.data?.data)
          ? raw.data.data
          : [];

      const unread = typeof raw?.unread_count === 'number'
        ? raw.unread_count
        : typeof raw?.data?.unread_count === 'number'
          ? raw.data.unread_count
          : 0;

      return {
        success: true,
        message: raw?.message || 'OK',
        data: {
          data: notifications,
          unread_count: unread
        }
      };
    } catch (error) {
      return {
        success: false,
        message: 'Failed to parse server response.',
        errors: { general: ['Response parsing error'] }
      };
    }
  }

  async getUnreadNotificationCount(): Promise<ApiResponse<{ unread_count: number }>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/notifications/unread-count`, {
      method: 'GET',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ unread_count: number }>(response);
  }

  async markNotificationAsRead(id: number): Promise<ApiResponse<{ message: string }>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/notifications/${id}/read`, {
      method: 'POST',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ message: string }>(response);
  }

  async markAllNotificationsAsRead(): Promise<ApiResponse<{ message: string }>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/notifications/mark-all-read`, {
      method: 'POST',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ message: string }>(response);
  }

  async deleteNotification(id: number): Promise<ApiResponse<{ message: string }>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/notifications/${id}`, {
      method: 'DELETE',
      headers: this.getHeaders()
    });

    return await this.handleResponse<{ message: string }>(response);
  }

  async createTestNotification(data: {
    type: string;
    title: string;
    message: string;
    is_important?: boolean;
    action_url?: string;
    action_text?: string;
  }): Promise<ApiResponse<Notification>> {
    const response = await fetch(`${API_BASE_URL}/medical-insurance/notifications/test`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    });

    return await this.handleResponse<Notification>(response);
  }
}

// Create and export a single instance
export const apiService = new ApiService();

// Export the class for testing purposes
export default ApiService;
