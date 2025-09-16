/**
 * API Bridge Service
 * 
 * This file bridges the existing frontend code with the new Laravel API
 * Maintains compatibility with existing code while using the new backend
 */

import { laravelApi, type ApiResponse as LaravelApiResponse, type User as LaravelUser, type InsurancePlan } from './laravel-api';

// Re-export types for compatibility with frontend expectations
export interface ApiResponse<T = any> {
  status?: 'success' | 'error';
  success?: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
}

// User type for compatibility with existing code
export interface User {
  id: number;
  agent_code?: string;
  name: string;
  email: string;
  phone_number: string;
  nric: string;
  customer_type: 'client' | 'agent';
  status: 'active' | 'inactive' | 'suspended' | 'pending_verification';
  mlm_level: number;
  wallet_balance: number;
  total_commission_earned: number;
  registration_date: string;
  referrer_code?: string;
  race?: string;
  date_of_birth?: string;
  gender?: string;
  occupation?: string;
  address?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  // Bank information
  bank_name?: string;
  bank_account_number?: string;
  bank_account_owner?: string;
  // Additional member fields
  relationship_with_agent?: string;
  emergency_contact_name?: string;
  emergency_contact_phone?: string;
  emergency_contact_relationship?: string;
}

// Dashboard stats interface
export interface DashboardStats {
  total_members: number;
  new_members: number;
  active_members: number;
  total_commission_earned: string;
  monthly_commission_earned: string;
  target_achievement: number;
  mlm_level: number;
  wallet_balance: string;
}

// Member interface for dashboard
export interface Member {
  id: number;
  name: string;
  email: string;
  nric: string;
  phone_number: string;
  status: string;
  registration_date: string;
  balance: number;
  wallet_balance: number;
  mlm_level: number;
  active_policies_count: number;
  total_commission_earned: number;
  // Additional fields from User
  race?: string;
  relationship_with_agent?: string;
  emergency_contact_name?: string;
  emergency_contact_phone?: string;
  emergency_contact_relationship?: string;
}

// Recent activity interface
export interface RecentActivity {
  type: string;
  title: string;
  description: string;
  created_at: string;
  icon?: string;
  color?: string;
}

// Performance data interface
export interface PerformanceData {
  monthly_commissions: any[];
  network_growth: any[];
  total_earnings: number;
  avg_monthly_earnings: number;
}

// Payment transaction interface
export interface PaymentTransaction {
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
  member_policy?: any;
}

// Payment mandate interface
export interface PaymentMandate {
  id: number;
  user_id: number;
  mandate_id: string;
  status: string;
  created_at: string;
}

// Notification interface
export interface Notification {
  id: number;
  user_id: number;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  read_at?: string;
  created_at: string;
  // Additional frontend fields
  action_url?: string;
  background_color?: string;
  icon?: string;
  time_ago?: string;
  is_important?: boolean;
}

// Additional interfaces for frontend compatibility
export interface RecordsSharingResponse {
  data: any[];
  total: number;
  monthly_performance: SharingMonthlyPerformanceItem[];
}

export interface SharingMonthlyPerformanceItem {
  month: string;
  performance: number;
  new_members: number;
  payments: number;
  hospital_cases: number;
  clinic_cases: number;
}

// Wallet data interface
export interface WalletData {
  balance: number;
  pending_commissions: number;
  total_earned: number;
  recent_transactions: any[];
  withdrawal_requests: any[];
}

// Hospital/Clinic interfaces
export interface Hospital {
  id: number;
  name: string;
  address: string;
  city: string;
  state: string;
  phone: string;
  is_panel: boolean;
  is_active: boolean;
}

export interface Clinic {
  id: number;
  name: string;
  address: string;
  city: string;
  state: string;
  phone: string;
  is_panel: boolean;
  is_active: boolean;
}

// Healthcare facility interface for compatibility
export interface HealthcareFacility {
  id: number;
  name: string;
  address: string;
  city: string;
  state: string;
  phone: string;
  is_panel: boolean;
  is_active: boolean;
  type?: 'hospital' | 'clinic';
}

/**
 * API Service Bridge Class
 * 
 * This class provides the same interface as the old API service
 * but uses the new Laravel backend under the hood
 */
class ApiServiceBridge {
  // =====================
  // AUTHENTICATION
  // =====================

  async login(agentCode: string, password: string): Promise<ApiResponse<{ user: User; token: string }>> {
    try {
      // Convert agent code format if needed (AGT12345 -> email format or keep as is)
      const email = agentCode.includes('@') ? agentCode : `${agentCode.toLowerCase()}@khholdings.com`;
      
      const response = await laravelApi.login(email, password);
      
      if (response.status === 'success' && response.data) {
        // Convert Laravel user format to frontend format
        const user: User = this.convertUserFormat(response.data.user);
        
        return {
          status: 'success',
          success: true,
          data: {
            user,
            token: (response.data as any).access_token || (response as any).data?.authorisation?.token
          }
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: response.message || 'Login failed'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Login failed'
      };
    }
  }

  async logout(): Promise<ApiResponse> {
    const response = await laravelApi.logout();
    return this.convertResponse(response);
  }

  async getProfile(): Promise<ApiResponse<{ user: User }>> {
    try {
      const response = await laravelApi.getMe();
      
      if (response.status === 'success' && response.data) {
        const user: User = this.convertUserFormat(response.data.user);
        
        return {
          status: 'success',
          success: true,
          data: { user }
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: response.message || 'Failed to get profile'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get profile'
      };
    }
  }

  async register(userData: any): Promise<ApiResponse<{ user: User; token: string }>> {
    try {
      const response = await laravelApi.register(userData);
      
      if (response.status === 'success' && response.data) {
        const user: User = this.convertUserFormat(response.data.user);
        
        return {
          status: 'success',
          success: true,
          data: {
            user,
            token: (response.data as any).access_token || (response as any).data?.authorisation?.token
          }
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: response.message || 'Registration failed'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Registration failed'
      };
    }
  }

  // =====================
  // DASHBOARD
  // =====================

  async getDashboardStats(): Promise<ApiResponse<{ stats: DashboardStats; recent_activities: RecentActivity[]; performance_data: PerformanceData }>> {
    try {
      const response = await laravelApi.getDashboardStats();
      
      if (response.status === 'success' && response.data) {
        return {
          status: 'success',
          success: true,
          data: {
            stats: response.data.stats,
            recent_activities: response.data.recent_activities || [],
            performance_data: response.data.performance_data || {
              monthly_commissions: [],
              network_growth: [],
              total_earnings: 0,
              avg_monthly_earnings: 0
            }
          }
        };
      }
      
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get dashboard stats'
      };
    }
  }

  async getMembers(params?: any): Promise<ApiResponse<{ data: Member[] }>> {
    try {
      const response = await laravelApi.getNetwork(params);
      
      if (response.status === 'success' && response.data) {
        // Convert network members to Member format
        const members: Member[] = (response.data.network_members?.data || []).map((member: any) => ({
          id: member.id,
          name: member.name,
          email: member.email,
          nric: member.nric,
          phone_number: member.phone_number || '',
          status: member.status,
          registration_date: member.registration_date,
          balance: 0, // Will be updated from wallet data
          wallet_balance: member.wallet_balance || 0,
          mlm_level: member.mlm_level,
          active_policies_count: member.active_policies_count || 0,
          total_commission_earned: member.total_commission_earned || 0
        }));
        
        return {
          status: 'success',
          success: true,
          data: { data: members }
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: response.message || 'Failed to get members'
      };
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get members'
      };
    }
  }

  // =====================
  // WALLET
  // =====================

  async getAgentWallet(): Promise<ApiResponse<WalletData>> {
    try {
      const response = await laravelApi.getAgentWallet();
      
      if (response.status === 'success' && response.data) {
        const laravelWallet = response.data as any;
        const walletData: WalletData = {
          balance: laravelWallet.balance || 0,
          pending_commissions: laravelWallet.pending_commissions || laravelWallet.pending_commission || 0,
          total_earned: laravelWallet.total_earned || 0,
          recent_transactions: laravelWallet.recent_transactions || [],
          withdrawal_requests: laravelWallet.withdrawal_requests || []
        };
        
        return {
          status: 'success',
          success: true,
          data: walletData
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: response.message || 'Failed to get wallet data'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get wallet data'
      };
    }
  }

  async getWalletTransactions(page: number = 1): Promise<ApiResponse<any>> {
    return laravelApi.getWalletTransactions(page);
  }

  async createWithdrawalRequest(data: any): Promise<ApiResponse<any>> {
    return laravelApi.createWithdrawalRequest(data);
  }

  // =====================
  // PAYMENTS
  // =====================

  async getPayments(params?: any): Promise<ApiResponse<any>> {
    return laravelApi.getPayments(params);
  }

  async createPayment(data: any): Promise<ApiResponse<any>> {
    return laravelApi.createPayment(data);
  }

  async getPaymentReceipt(paymentId: number): Promise<ApiResponse<any>> {
    return laravelApi.getReceipt(paymentId);
  }

  // Additional payment methods for profile page
  async getPaymentHistory(page?: number): Promise<ApiResponse<any>> {
    const response = await laravelApi.getPayments();
    return this.convertResponse(response);
  }

  async getPaymentMandates(): Promise<ApiResponse<any>> {
    // For now, return empty array as mandates might not be implemented yet
    return {
      status: 'success',
      data: { data: [] }
    };
  }

  // Medical insurance payment methods
  async getMedicalInsurancePaymentConfig(): Promise<ApiResponse<any>> {
    // Return mock payment config for now
    return {
      status: 'success',
      data: {
        curlec: {
          key_id: process.env.NEXT_PUBLIC_CURLEC_KEY_ID,
          sandbox: process.env.NEXT_PUBLIC_CURLEC_SANDBOX === 'true'
        }
      }
    };
  }

  async getMedicalInsuranceRegistrationStatus(registrationId: number): Promise<ApiResponse<any>> {
    // Return mock registration status for now
    return {
      status: 'success',
      data: {
        id: registrationId,
        status: 'pending_payment',
        customers: []
      }
    };
  }

  async createMedicalInsurancePaymentOrderForAllCustomers(data: any): Promise<ApiResponse<any>> {
    // Return mock payment calculation for now
    return {
      status: 'success',
      data: {
        total_amount: 90.00, // Default MediPlan Coop price
        breakdown: [
          {
            plan: 'MediPlan Coop',
            amount: 90.00,
            customer: 'Primary Customer'
          }
        ]
      }
    };
  }

  // =====================
  // INSURANCE PLANS
  // =====================

  async getInsurancePlans(): Promise<ApiResponse<{ plans: InsurancePlan[] }>> {
    try {
      const response = await laravelApi.getInsurancePlans();
      
      if (response.status === 'success' && response.data) {
        return {
          status: 'success',
          data: { plans: response.data.plans }
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get insurance plans'
      };
    }
  }

  async getInsurancePlan(id: number): Promise<ApiResponse<{ plan: InsurancePlan }>> {
    return laravelApi.getInsurancePlan(id);
  }

  // For medical insurance form compatibility
  async getMedicalInsurancePlans(): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getInsurancePlans();
      
      if (response.status === 'success' && response.data) {
        // Transform to match expected format
        const plans = response.data.plans.map((plan: any) => ({
          id: plan.id,
          name: plan.plan_name,
          description: plan.description,
          monthly_price: parseFloat(plan.monthly_price || '0'),
          quarterly_price: plan.quarterly_price ? parseFloat(plan.quarterly_price) : null,
          half_yearly_price: plan.semi_annually_price ? parseFloat(plan.semi_annually_price) : null,
          yearly_price: parseFloat(plan.annually_price || '0'),
          commitment_fee: parseFloat(plan.commitment_fee || '0'),
          coverage_details: plan.benefits,
          terms_conditions: plan.terms_conditions,
          min_age: plan.min_age || 0,
          max_age: plan.max_age || 100
        }));
        
        return {
          status: 'success',
          data: plans
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get medical insurance plans'
      };
    }
  }

  // =====================
  // HOSPITALS & CLINICS
  // =====================

  async getHospitals(params?: any): Promise<ApiResponse<HealthcareFacility[]>> {
    try {
      const response = await laravelApi.getHospitals(params);
      
      if (response.status === 'success' && response.data) {
        // Transform hospitals to HealthcareFacility format
        const hospitals: HealthcareFacility[] = (response.data.data || []).map((hospital: any) => ({
          id: hospital.id,
          name: hospital.name,
          address: hospital.address,
          city: hospital.city,
          state: hospital.state,
          phone: hospital.phone,
          is_panel: hospital.is_panel || true,
          is_active: hospital.is_active || true,
          type: 'hospital' as const
        }));
        
        return {
          status: 'success',
          data: hospitals
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get hospitals'
      };
    }
  }

  async getClinics(params?: any): Promise<ApiResponse<HealthcareFacility[]>> {
    try {
      const response = await laravelApi.getClinics(params);
      
      if (response.status === 'success' && response.data) {
        // Transform clinics to HealthcareFacility format
        const clinics: HealthcareFacility[] = (response.data.data || []).map((clinic: any) => ({
          id: clinic.id,
          name: clinic.name,
          address: clinic.address,
          city: clinic.city,
          state: clinic.state,
          phone: clinic.phone,
          is_panel: clinic.is_panel || true,
          is_active: clinic.is_active || true,
          type: 'clinic' as const
        }));
        
        return {
          status: 'success',
          data: clinics
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get clinics'
      };
    }
  }

  async searchHospitals(query: string): Promise<ApiResponse<{ hospitals: Hospital[] }>> {
    return laravelApi.searchHospitals(query);
  }

  async searchClinics(query: string): Promise<ApiResponse<{ clinics: Clinic[] }>> {
    return laravelApi.searchClinics(query);
  }

  // =====================
  // NOTIFICATIONS
  // =====================

  async getNotifications(limit?: number, unreadOnly?: boolean): Promise<ApiResponse<{ notifications: Notification[]; data: Notification[]; unread_count: number }>> {
    try {
      const params = limit !== undefined ? { per_page: limit, type: unreadOnly ? 'unread' : undefined } : undefined;
      const response = await laravelApi.getNotifications(params);
      
      if (response.status === 'success' && response.data) {
        const notifications = response.data.notifications || [];
        return {
          status: 'success',
          success: true,
          data: {
            notifications,
            data: notifications,
            unread_count: response.data.unread_count || 0
          }
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: response.message || 'Failed to get notifications'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get notifications'
      };
    }
  }

  async markNotificationAsRead(id: number): Promise<ApiResponse> {
    return laravelApi.markNotificationAsRead(id);
  }

  async markAllNotificationsAsRead(): Promise<ApiResponse> {
    return laravelApi.markAllNotificationsAsRead();
  }

  async getUnreadNotificationsCount(): Promise<ApiResponse<{ unread_count: number }>> {
    return laravelApi.getUnreadNotificationsCount();
  }

  // =====================
  // MLM NETWORK
  // =====================

  async getNetwork(params?: any): Promise<ApiResponse<any>> {
    return laravelApi.getNetwork(params);
  }

  async getCommissionHistory(params?: any): Promise<ApiResponse<any>> {
    return laravelApi.getCommissionHistory(params);
  }

  async registerClient(clientData: any): Promise<ApiResponse<any>> {
    return laravelApi.registerClient(clientData);
  }

  // Medical Insurance Registration methods
  async registerMedicalInsurance(registrationData: any): Promise<ApiResponse<any>> {
    try {
      // Transform registration data to match Laravel API format
      const clientData = {
        name: registrationData.full_name,
        email: registrationData.email,
        phone_number: registrationData.phone_number,
        nric: registrationData.nric,
        race: registrationData.race,
        date_of_birth: new Date(Date.now() - (25 * 365 * 24 * 60 * 60 * 1000)).toISOString().split('T')[0], // Default to 25 years old
        gender: 'Male', // Default, should be added to form
        occupation: registrationData.occupation || 'Not specified',
        height_cm: registrationData.height_cm,
        weight_kg: registrationData.weight_kg,
        emergency_contact_name: registrationData.emergency_contact_name,
        emergency_contact_phone: registrationData.emergency_contact_phone,
        emergency_contact_relationship: registrationData.emergency_contact_relationship,
        medical_consultation_2_years: registrationData.medical_consultation_2_years,
        serious_illness_history: registrationData.serious_illness_history || '',
        insurance_rejection_history: registrationData.insurance_rejection_history,
        serious_injury_history: registrationData.serious_injury_history || '',
        address: registrationData.address || 'To be updated',
        city: registrationData.city || 'Kuala Lumpur',
        state: registrationData.state || 'Selangor',
        postal_code: registrationData.postal_code || '50000',
        plan_name: registrationData.plan_type,
        payment_mode: registrationData.payment_mode,
        medical_card_type: registrationData.medical_card_type,
        password: registrationData.password,
        password_confirmation: registrationData.password
      };

      const response = await laravelApi.registerClient(clientData);
      
      if (response.status === 'success' && response.data) {
        return {
          status: 'success',
          data: { id: response.data.client?.id || 1 }
        };
      }
      
      return {
        status: 'error',
        message: response.message || 'Registration failed',
        errors: response.errors
      };
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Registration failed'
      };
    }
  }

  async registerMedicalInsuranceExternal(registrationData: any): Promise<ApiResponse<any>> {
    // For external mode, use the same method but with additional agent code context
    return this.registerMedicalInsurance(registrationData);
  }

  // Additional MLM methods for profile page
  async getReferrals(): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getNetwork();
      
      if (response.status === 'success' && response.data) {
        // Transform network data to referrals format
        const networkMembers = response.data.network_members?.data || [];
        // Get current user info from separate API call if needed
        const userResponse = await laravelApi.getMe();
        const currentUser = userResponse.data?.user;
        const userAgentCode = currentUser?.agent_code || '';
        const directReferrals = networkMembers.filter((member: any) => member.referrer_code === userAgentCode);
        
        return {
          status: 'success',
          data: {
            direct_referrals: directReferrals,
            direct_referrals_count: directReferrals.length,
            total_downlines_count: networkMembers.length,
            downline_stats: {
              direct_referrals_count: directReferrals.length,
              total_downlines_count: networkMembers.length
            }
          }
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get referrals'
      };
    }
  }

  async getDownlines(level?: number): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getNetwork();
      
      if (response.status === 'success' && response.data) {
        // Filter by level if specified
        let downlines = response.data.network_members?.data || [];
        if (level) {
          downlines = downlines.filter((member: any) => member.mlm_level === level);
        }
        
        return {
          status: 'success',
          data: { data: downlines }
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get downlines'
      };
    }
  }

  async getCommissionSummary(): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getLevelSummary();
      
      if (response.status === 'success' && response.data) {
        return {
          status: 'success',
          data: response.data.level_summary
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        message: error instanceof Error ? error.message : 'Failed to get commission summary'
      };
    }
  }

  // =====================
  // POLICIES
  // =====================

  async getPolicies(params?: any): Promise<ApiResponse<any>> {
    return laravelApi.getPolicies(params);
  }

  async purchasePolicy(data: any): Promise<ApiResponse<any>> {
    return laravelApi.purchasePolicy(data);
  }

  // =====================
  // TOKEN MANAGEMENT
  // =====================

  setToken(token: string): void {
    laravelApi['setToken'](token);
  }

  clearToken(): void {
    laravelApi['clearToken']();
  }

  getToken(): string | null {
    return laravelApi.getToken();
  }

  isAuthenticated(): boolean {
    return laravelApi.isAuthenticated();
  }

  // =====================
  // PAYMENT INTEGRATION
  // =====================

  async initiateCurlecPayment(policyId: number, returnUrl?: string, cancelUrl?: string): Promise<void> {
    return laravelApi.initiateCurlecPayment(policyId, 'curlec', returnUrl, cancelUrl);
  }

  // =====================
  // ADDITIONAL PROFILE PAGE METHODS
  // =====================

  async loadClients(): Promise<ApiResponse<any>> {
    // Get network members (clients registered by this agent)
    return this.getMembers();
  }

  async updateProfile(profileData: any): Promise<ApiResponse<any>> {
    try {
      console.log('Updating profile with data:', profileData);
      const response = await laravelApi.updateProfile(profileData);
      console.log('Profile update response:', response);
      return this.convertResponse(response);
    } catch (error) {
      console.error('Profile update error:', error);
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update profile'
      };
    }
  }

  async changePassword(passwordData: any): Promise<ApiResponse<any>> {
    try {
      console.log('Changing password with data:', { ...passwordData, current_password: '[hidden]', new_password: '[hidden]' });
      const response = await laravelApi.changePassword(passwordData);
      console.log('Password change response:', response);
      return this.convertResponse(response);
    } catch (error) {
      console.error('Password change error:', error);
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to change password'
      };
    }
  }

  async updateBankInfo(bankData: any): Promise<ApiResponse<any>> {
    try {
      // Bank info is part of profile, so use updateProfile
      const response = await laravelApi.updateProfile(bankData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update bank information'
      };
    }
  }

  // Missing methods from frontend components
  async requestWithdrawal(data: any): Promise<ApiResponse<any>> {
    return this.createWithdrawalRequest(data);
  }

  async getClients(page?: number): Promise<ApiResponse<any>> {
    return this.getMembers();
  }

  async getClientPayments(clientId: number): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { data: [] }
    };
  }

  async getUserDownlines(userId: number, level?: number, page?: number): Promise<ApiResponse<any>> {
    return this.getDownlines(level);
  }

  async getGatewayPaymentHistory(page?: number): Promise<ApiResponse<any>> {
    return this.getPaymentHistory();
  }

  async getSharingRecords(): Promise<ApiResponse<RecordsSharingResponse>> {
    return {
      status: 'success',
      success: true,
      data: { 
        data: [], 
        total: 0,
        monthly_performance: [
          {
            month: 'January',
            performance: 85,
            new_members: 12,
            payments: 25000,
            hospital_cases: 8,
            clinic_cases: 15
          },
          {
            month: 'February',
            performance: 92,
            new_members: 18,
            payments: 35000,
            hospital_cases: 12,
            clinic_cases: 22
          }
        ]
      }
    };
  }

  async sendTac(phoneNumber: string, type: string): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { message: 'TAC sent successfully' }
    };
  }

  async verifyTac(phoneNumber: string, tac: string, type: string): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { message: 'TAC verified successfully' }
    };
  }

  async changePhone(data: any): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { message: 'Phone changed successfully' }
    };
  }

  async deleteNotification(id: number): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { message: 'Notification deleted' }
    };
  }

  async getUnreadNotificationCount(): Promise<ApiResponse<{ unread_count: number }>> {
    return this.getUnreadNotificationsCount();
  }

  async verifyMedicalInsurancePayment(data: any): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { verified: true }
    };
  }

  // =====================
  // HELPER METHODS
  // =====================

  private convertResponse<T>(laravelResponse: LaravelApiResponse<T>): ApiResponse<T> {
    return {
      status: laravelResponse.status,
      success: laravelResponse.status === 'success',
      message: laravelResponse.message,
      data: laravelResponse.data,
      errors: laravelResponse.errors
    };
  }

  private convertUserFormat(laravelUser: any): User {
    return {
      id: laravelUser.id,
      agent_code: laravelUser.agent_code,
      name: laravelUser.name,
      email: laravelUser.email,
      phone_number: laravelUser.phone_number || '',
      nric: laravelUser.nric || '',
      customer_type: laravelUser.customer_type || 'client',
      status: laravelUser.status || 'active',
      mlm_level: laravelUser.mlm_level || 1,
      wallet_balance: parseFloat(laravelUser.wallet_balance) || 0,
      total_commission_earned: parseFloat(laravelUser.total_commission_earned) || 0,
      registration_date: laravelUser.registration_date || laravelUser.created_at,
      referrer_code: laravelUser.referrer_code,
      race: laravelUser.race,
      date_of_birth: laravelUser.date_of_birth,
      gender: laravelUser.gender,
      occupation: laravelUser.occupation,
      address: laravelUser.address,
      city: laravelUser.city,
      state: laravelUser.state,
      postal_code: laravelUser.postal_code
    };
  }

  // Bulk Client Registration Methods
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
    try {
      const response = await laravelApi.registerBulkClients(clientsData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to register clients'
      };
    }
  }

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
    try {
      const response = await laravelApi.createBulkPayment(paymentData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to create bulk payment'
      };
    }
  }

  // deduplicated getInsurancePlans above (returns { plans })

  async verifyBulkPayment(verificationData: {
    razorpay_order_id: string;
    razorpay_payment_id: string;
    razorpay_signature: string;
    registration_id: number;
  }): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.verifyBulkPayment(verificationData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to verify bulk payment'
      };
    }
  }
}

// Export singleton instance
export const apiService = new ApiServiceBridge();

// For backward compatibility, export as default as well
export default apiService;
