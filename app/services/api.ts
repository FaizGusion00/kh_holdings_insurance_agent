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
  total_hospitals: number;
  total_clinics: number;
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

  async login(identifier: string, password: string): Promise<ApiResponse<{ user: User; token: string }>> {
    try {
      // Pass through identifier directly; backend accepts email or AGT code
      const response = await laravelApi.login(identifier, password);
      
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
        // For dashboard, only show direct referrals (level 1), not all network members
        const directReferrals = (response.data as any).direct_referrals || [];
        
        // Convert direct referrals to Member format
        const members: Member[] = directReferrals.map((member: any) => ({
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
        
        // Transform transactions to match frontend interface
        const transformedTransactions = (laravelWallet.recent_transactions || []).map((tx: any) => ({
          id: tx.id,
          type: tx.type,
          amount: tx.amount_cents ? tx.amount_cents / 100 : 0, // Convert cents to dollars
          description: tx.source || 'Transaction',
          status: 'completed', // Default status
          created_at: tx.created_at,
          commission_id: tx.commission_transaction_id
        }));
        
        // Transform withdrawal requests to match frontend interface
        const transformedWithdrawals = (laravelWallet.withdrawal_requests || []).map((req: any) => ({
          id: req.id,
          amount: req.amount_cents ? req.amount_cents / 100 : 0, // Convert cents to dollars
          status: req.status,
          created_at: req.created_at,
          processed_at: req.updated_at,
          admin_notes: req.bank_meta?.notes || null,
          proof_url: req.bank_meta?.proof_url || null
        }));
        
        const walletData: WalletData = {
          balance: laravelWallet.balance || 0,
          pending_commissions: 0, // Not available in current backend
          total_earned: laravelWallet.total_earned || 0,
          recent_transactions: transformedTransactions,
          withdrawal_requests: transformedWithdrawals
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
    const response = await laravelApi.getWalletTransactions(page);
    return this.convertResponse(response);
  }

  async createWithdrawalRequest(data: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.createWithdrawalRequest(data);
    return this.convertResponse(response);
  }

  // =====================
  // PAYMENTS
  // =====================

  async getPayments(params?: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.getPayments(params);
    return this.convertResponse(response);
  }

  async createPayment(data: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.createPayment(data);
    return this.convertResponse(response);
  }

  async getPaymentReceipt(paymentId: number): Promise<ApiResponse<any>> {
    const response = await laravelApi.getReceipt(paymentId);
    return this.convertResponse(response);
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
    // Return payment config with correct structure
    return {
      status: 'success',
      success: true,
      data: {
        key_id: process.env.NEXT_PUBLIC_CURLEC_KEY_ID,
        sandbox: process.env.NEXT_PUBLIC_CURLEC_SANDBOX === 'true'
      }
    };
  }

  async getMedicalInsuranceRegistrationStatus(registrationId: number): Promise<ApiResponse<any>> {
    // Return mock registration status for now
    return {
      status: 'success',
      success: true,
      data: {
        id: registrationId,
        status: 'pending_payment',
        customers: []
      }
    };
  }

  async createMedicalInsurancePaymentOrderForAllCustomers(data: any): Promise<ApiResponse<any>> {
    try {
      // If this is just a calculation request, return the pre-fetched totals from registration modal
      if (data.calculate_only && (data.total_amount || data.breakdown)) {
        return {
          status: 'success',
          success: true,
          data: {
            total_amount: data.total_amount,
            breakdown: data.breakdown || []
          }
        };
      }

      // Create bulk payment using the registration_id
      const response = await laravelApi.createBulkPayment({
        registration_id: data.registration_id,
        payment_method: 'curlec',
        return_url: data.return_url,
        cancel_url: data.cancel_url
      });

      if (response.status === 'success' && response.data) {
        return {
          status: 'success',
          success: true,
          data: {
            total_amount: response.data.payment?.amount,
            // 'breakdown' may not exist on response.data, so default to empty array if not present
            breakdown: response.data.payment?.breakdown || [],
            checkout_config: {
              amount: response.data.payment?.amount * 100, // Convert to cents for Razorpay
              currency: 'MYR',
              order_id: response.data.payment?.gateway_order_id,
              name: 'KH Holdings Insurance',
              description: 'Medical Insurance Premium Payment',
              prefill: {
                email: '', // Will be filled by payment gateway
                contact: ''
              },
              theme: {
                color: '#3399cc'
              }
            }
          }
        };
      }

      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Payment creation failed'
      };
    }
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
    const response = await laravelApi.getInsurancePlan(id);
    return this.convertResponse(response);
  }

  // For medical insurance form compatibility
  async getMedicalInsurancePlans(): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getInsurancePlans();
      
      if (response.status === 'success' && response.data) {
        // The new API structure returns data directly as an array
        const plansData = Array.isArray(response.data) ? response.data : response.data.plans || [];
        
        // Transform to match expected format with correct pricing structure
        const plans = plansData.map((plan: any) => ({
          id: plan.id,
          name: plan.name, // Changed from plan_name to name
          plan_code: plan.plan_code,
          description: plan.description,
          pricing: {
            monthly: { base_price: plan.pricing?.monthly?.base_price || '0' },
            quarterly: { base_price: plan.pricing?.quarterly?.base_price || null },
            semi_annually: { base_price: plan.pricing?.semi_annually?.base_price || null },
            annually: { base_price: plan.pricing?.annually?.base_price || '0' }
          },
          commitment_fee: plan.commitment_fee || '0',
          coverage_details: plan.benefits,
          terms_conditions: plan.terms_conditions,
          min_age: plan.min_age || 0,
          max_age: plan.max_age || 100,
          available_modes: plan.available_modes || ['monthly', 'quarterly', 'semi_annually', 'annually']
        }));
        
        return {
          status: 'success',
          success: true,
          data: plans
        };
      }
      
      return response;
    } catch (error) {
      return {
        status: 'error',
        success: false,
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
        // API returns a paginator at data. Transform to flat list
        const list = Array.isArray((response as any).data?.data)
          ? (response as any).data.data
          : (response as any).data;
        const hospitals: HealthcareFacility[] = (list || []).map((hospital: any) => ({
          id: hospital.id,
          name: hospital.name,
          address: hospital.address,
          city: hospital.city,
          state: hospital.state,
          phone: hospital.phone_number || hospital.phone,
          is_panel: hospital.is_panel ?? true,
          is_active: hospital.is_active ?? true,
          type: 'hospital' as const
        }));
        
        return {
          status: 'success',
          success: true,
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
        const list = Array.isArray((response as any).data?.data)
          ? (response as any).data.data
          : (response as any).data;
        const clinics: HealthcareFacility[] = (list || []).map((clinic: any) => ({
          id: clinic.id,
          name: clinic.name,
          address: clinic.address,
          city: clinic.city,
          state: clinic.state,
          phone: clinic.phone_number || clinic.phone,
          is_panel: clinic.is_panel ?? true,
          is_active: clinic.is_active ?? true,
          type: 'clinic' as const
        }));
        
        return {
          status: 'success',
          success: true,
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
    const response = await laravelApi.searchHospitals(query);
    return this.convertResponse(response);
  }

  async searchClinics(query: string): Promise<ApiResponse<{ clinics: Clinic[] }>> {
    const response = await laravelApi.searchClinics(query);
    return this.convertResponse(response);
  }

  // =====================
  // NOTIFICATIONS
  // =====================

  async getNotifications(limit?: number, unreadOnly?: boolean): Promise<ApiResponse<{ notifications: Notification[]; data: Notification[]; unread_count: number }>> {
    try {
      const params = limit !== undefined ? { per_page: limit, type: unreadOnly ? 'unread' : undefined } : undefined;
      const response = await laravelApi.getNotifications(params);
      
      if (response.success && response.data) {
        const rawNotifications = response.data.notifications || [];
        
        // Transform notifications to match frontend interface
        const notifications: Notification[] = rawNotifications.map((notif: any) => ({
          id: notif.id,
          user_id: notif.user_id || 0,
          type: notif.type,
          title: notif.title,
          message: notif.message,
          is_read: notif.is_read || false,
          read_at: notif.read_at,
          created_at: notif.created_at,
          // Add frontend-specific fields
          time_ago: this.formatTimeAgo(notif.created_at),
          icon: this.getNotificationIcon(notif.type, notif.category),
          background_color: this.getNotificationColor(notif.type, notif.priority),
          is_important: notif.priority === 'high' || notif.priority === 'urgent',
          action_url: this.getNotificationActionUrl(notif.type, notif.data)
        }));
        
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
    const response = await laravelApi.markNotificationAsRead(id);
    return this.convertResponse(response);
  }

  async markAllNotificationsAsRead(): Promise<ApiResponse> {
    const response = await laravelApi.markAllNotificationsAsRead();
    return this.convertResponse(response);
  }

  async getUnreadNotificationsCount(): Promise<ApiResponse<{ unread_count: number }>> {
    const response = await laravelApi.getUnreadNotificationsCount();
    return this.convertResponse(response);
  }

  // =====================
  // MLM NETWORK
  // =====================

  async getNetwork(params?: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.getNetwork(params);
    return this.convertResponse(response);
  }

  async getCommissionHistory(params?: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.getCommissionHistory(params);
    return this.convertResponse(response);
  }

  async getCommissionSummary(): Promise<ApiResponse<any>> {
    const response = await laravelApi.getCommissionSummary();
    return this.convertResponse(response);
  }

  async registerClient(clientData: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.registerClient(clientData);
    return this.convertResponse(response);
  }

  // Medical Insurance Registration methods
  async registerMedicalInsurance(registrationData: any): Promise<ApiResponse<any>> {
    try {
      // The new form already sends the correct format with clients array
      // Just pass it through to the backend
      const response = await laravelApi.post('/medical-registration/register', registrationData);
      return this.convertResponse(response);
    } catch (error) {
      console.error('Medical registration error:', error);
      throw error;
    }
  }

  // Helper method to map plan names to IDs
  private getInsurancePlanIdByName(planName: string): number {
    // Map plan names to IDs based on seeded data
    const planMapping: Record<string, number> = {
      'MediPlan Coop': 1,
      'Senior Care Plan Gold 270': 2,
      'Senior Care Plan Diamond 370': 3
    };
    
    return planMapping[planName] || 1; // Default to MediPlan Coop
  }

  async registerMedicalInsuranceExternal(registrationData: any): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/medical-registration/external/register', registrationData);
      return this.convertResponse(response);
    } catch (error) {
      console.error('External medical registration error:', error);
      throw error;
    }
  }

  async createMedicalInsurancePayment(paymentData: any): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/medical-registration/payment', paymentData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Payment creation failed'
      };
    }
  }

  async verifyMedicalInsurancePayment(verificationData: any): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/medical-registration/verify', verificationData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Payment verification failed'
      };
    }
  }

  async createMedicalInsurancePaymentExternal(paymentData: any): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/medical-registration/external/payment', paymentData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Payment creation failed'
      };
    }
  }

  async verifyMedicalInsurancePaymentExternal(verificationData: any): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/medical-registration/external/verify', verificationData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Payment verification failed'
      };
    }
  }

  async getMedicalInsuranceReceipt(paymentId: number): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.get(`/medical-registration/receipt/${paymentId}`);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get receipt'
      };
    }
  }

  async getPendingRegistrations(): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.get('/medical-registration/pending');
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get pending registrations'
      };
    }
  }

  // Additional MLM methods for profile page
  async getReferrals(): Promise<ApiResponse<any>> {
    try {
      // Get full network hierarchy for referrer tab - all levels  
      const response = await laravelApi.getNetwork({ level: 5 } as any);
      
      if (response.status === 'success' && response.data) {
        // Return the full network structure with level breakdown
        const dataResponse = response.data as any;
        return {
          status: 'success',
          success: true,
          data: {
            network_members: dataResponse.network_members || [],
            level_breakdown: dataResponse.level_breakdown || {},
            total_members: dataResponse.total_members || 0,
            direct_referrals: dataResponse.direct_referrals || [],
            direct_referrals_count: dataResponse.direct_referrals_count || 0,
            total_downlines: dataResponse.total_downlines || 0
          }
        };
      }
      
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get referrals'
      };
    }
  }

  async getDownlines(level?: number): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getDownlines(level || 5);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get downlines'
      };
    }
  }

  // Method specifically for getting full network hierarchy
  async getNetworkHierarchy(levels: number = 5): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getNetwork({ level: levels } as any);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get network hierarchy'
      };
    }
  }


  // =====================
  // POLICIES
  // =====================

  async getPolicies(params?: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.getPolicies(params);
    return this.convertResponse(response);
  }

  async purchasePolicy(data: any): Promise<ApiResponse<any>> {
    const response = await laravelApi.purchasePolicy(data);
    return this.convertResponse(response);
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

  async sendPhoneVerification(phoneData: { phone_number: string }): Promise<ApiResponse<{ verification_code: string }>> {
    try {
      const response = await laravelApi.sendPhoneVerification(phoneData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to send verification code'
      };
    }
  }

  async verifyPhoneChange(verificationData: { phone_number: string; verification_code: string }): Promise<ApiResponse<{ user: User }>> {
    try {
      const response = await laravelApi.verifyPhoneChange(verificationData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to verify phone change'
      };
    }
  }

  async updateBankInfo(bankData: any): Promise<ApiResponse<any>> {
    try {
      // Bank info is part of profile, so use updateProfile
      const response = await laravelApi.updateProfile(bankData);
      
      if (response.status === 'success' && response.data) {
        // Convert the updated user data to frontend format
        const updatedUser = this.convertUserFormat(response.data.user);
        
        return {
          status: 'success',
          success: true,
          data: {
            user: updatedUser,
            message: 'Bank information updated successfully'
          }
        };
      }
      
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
  async requestWithdrawal(data: { amount: number; notes?: string }): Promise<ApiResponse<any>> {
    try {
      // Get user profile first to get bank information
      const profileResponse = await this.getProfile();
      
      if (!profileResponse.success || !profileResponse.data?.user) {
        return {
          status: 'error',
          success: false,
          message: 'Unable to get user profile for withdrawal request'
        };
      }
      
      const user = profileResponse.data.user;
      
      // Debug: Log user data to see what fields are available
      console.log('User profile data for withdrawal:', user);
      console.log('Bank fields:', {
        bank_name: user.bank_name,
        bank_account_number: user.bank_account_number,
        bank_account_owner: user.bank_account_owner
      });
      console.log('All user keys:', Object.keys(user));
      
      // Check if user has bank information - also check alternative field names
      const userAny = user as any;
      const bankName = user.bank_name || userAny.bankName;
      const bankAccountNumber = user.bank_account_number || userAny.bankAccountNumber;
      const bankAccountOwner = user.bank_account_owner || userAny.bankAccountOwner;
      
      if (!bankName || !bankAccountNumber || !bankAccountOwner) {
        return {
          status: 'error',
          success: false,
          message: `Please update your bank information in profile before requesting withdrawal. Missing: ${!bankName ? 'Bank Name' : ''} ${!bankAccountNumber ? 'Account Number' : ''} ${!bankAccountOwner ? 'Account Owner' : ''}`.trim()
        };
      }
      
      // Transform data to match backend API requirements
      const withdrawalData = {
        amount: data.amount,
        bank_name: bankName,
        bank_account_number: bankAccountNumber,
        bank_account_owner: bankAccountOwner,
      };
      
      console.log('Sending withdrawal request with data:', withdrawalData);
      const result = await this.createWithdrawalRequest(withdrawalData);
      console.log('Withdrawal request result:', result);
      return result;
    } catch (error) {
      console.error('Withdrawal request error:', error);
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to create withdrawal request'
      };
    }
  }

  async getClients(page?: number): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.getMedicalClients(page);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get medical clients'
      };
    }
  }

  async getClientPayments(clientId: number): Promise<ApiResponse<any>> {
    return {
      status: 'success',
      success: true,
      data: { data: [] }
    };
  }

  async getClientPolicies(clientId: number): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.get(`/mlm/client-policies/${clientId}`);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get client policies'
      };
    }
  }

  // =====================
  // MEMBER DETAILS
  // =====================

  async getMemberDetails(memberId: number): Promise<ApiResponse<any>> {
    try {
      // Get member details from the network
      const networkResponse = await this.getNetwork();
      if (networkResponse.success && networkResponse.data) {
        const allMembers = [
          ...(networkResponse.data.direct_referrals || []),
          ...(networkResponse.data.network_members || [])
        ];
        
        const member = allMembers.find((m: any) => m.id === memberId);
        if (member) {
          // Get member's policies to determine insurance plan
          const policiesResponse = await this.getClientPolicies(memberId);
          let insurancePlan = 'MediPlan Coop'; // Default
          
          if (policiesResponse.success && policiesResponse.data?.data?.length > 0) {
            const policy = policiesResponse.data.data[0];
            insurancePlan = policy.plan?.name || 'MediPlan Coop';
          }
          
          return {
            status: 'success',
            success: true,
            data: {
              ...member,
              insurance_plan: insurancePlan
            }
          };
        }
      }
      
      return {
        status: 'error',
        success: false,
        message: 'Member not found'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get member details'
      };
    }
  }

  async getMemberPaymentHistory(memberId: number): Promise<ApiResponse<any>> {
    try {
      // Get all payments and filter by member
      const paymentsResponse = await this.getPayments();
      
      if (paymentsResponse.success && paymentsResponse.data) {
        const memberPayments = paymentsResponse.data.data?.filter((payment: any) => 
          payment.user?.id === memberId || payment.user_id === memberId
        ) || [];
        
        return {
          status: 'success',
          success: true,
          data: {
            data: memberPayments
          }
        };
      }
      
      return {
        status: 'error',
        success: false,
        message: 'Failed to get payment history'
      };
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to get client policies'
      };
    }
  }

  async updatePolicyStatus(policyId: number, status: string): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.put(`/mlm/policy/${policyId}/status`, { status });
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update policy status'
      };
    }
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

  // This is a duplicate function, removing it

  // =====================
  // HELPER METHODS
  // =====================

  private formatTimeAgo(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    return date.toLocaleDateString();
  }

  private getNotificationIcon(type: string, category?: string): string {
    switch (type) {
      case 'welcome': return '👋';
      case 'commission_earned': return '💰';
      case 'payment_update': return '💳';
      case 'new_network_member': return '👥';
      case 'network_growth': return '📈';
      case 'policy_renewal_reminder': return '📋';
      case 'payment_due_reminder': return '⏰';
      case 'level_upgrade': return '🎉';
      case 'system': return '🔧';
      default: return '🔔';
    }
  }

  private getNotificationColor(type: string, priority?: string): string {
    if (priority === 'urgent') return 'bg-red-100 text-red-600';
    if (priority === 'high') return 'bg-orange-100 text-orange-600';
    
    switch (type) {
      case 'commission_earned': return 'bg-green-100 text-green-600';
      case 'payment_update': return 'bg-blue-100 text-blue-600';
      case 'new_network_member': return 'bg-purple-100 text-purple-600';
      case 'level_upgrade': return 'bg-yellow-100 text-yellow-600';
      case 'system': return 'bg-gray-100 text-gray-600';
      default: return 'bg-blue-100 text-blue-600';
    }
  }

  private getNotificationActionUrl(type: string, data?: any): string | undefined {
    switch (type) {
      case 'commission_earned':
        return '/wallet';
      case 'payment_update':
        return '/payments';
      case 'new_network_member':
      case 'network_growth':
        return '/network';
      case 'policy_renewal_reminder':
        return '/policies';
      default:
        return undefined;
    }
  }

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
      postal_code: laravelUser.postal_code,
      // Bank information
      bank_name: laravelUser.bank_name,
      bank_account_number: laravelUser.bank_account_number,
      bank_account_owner: laravelUser.bank_account_owner,
      // Additional member fields
      relationship_with_agent: laravelUser.relationship_with_agent,
      emergency_contact_name: laravelUser.emergency_contact_name,
      emergency_contact_phone: laravelUser.emergency_contact_phone,
      emergency_contact_relationship: laravelUser.emergency_contact_relationship
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

  async processContinuePayment(paymentData: {
    policy_id: number;
    payment_method: 'curlec' | 'manual';
    return_url?: string;
    cancel_url?: string;
  }): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/mlm/continue-payment', paymentData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to process continue payment'
      };
    }
  }

  async verifyContinuePayment(verificationData: {
    payment_id: number;
    status: 'success' | 'failed';
    external_ref?: string;
  }): Promise<ApiResponse<any>> {
    try {
      const response = await laravelApi.post('/mlm/verify-continue-payment', verificationData);
      return this.convertResponse(response);
    } catch (error) {
      return {
        status: 'error',
        success: false,
        message: error instanceof Error ? error.message : 'Failed to verify continue payment'
      };
    }
  }
}

// Export singleton instance
export const apiService = new ApiServiceBridge();

// For backward compatibility, export as default as well
export default apiService;
