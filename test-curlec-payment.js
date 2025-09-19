const axios = require('axios');

// Test Curlec Payment Integration
async function testCurlecPayment() {
  try {
    console.log('Testing Curlec Payment Integration...');
    
    // 1. Register an agent
    console.log('1. Registering a test agent...');
    const timestamp = Date.now();
    const agentRegResponse = await axios.post('http://localhost:8000/api/v1/auth/register', {
      name: `Test Agent ${timestamp}`,
      email: `test.agent.${timestamp}@example.com`,
      phone_number: `01${Math.floor(10000000 + Math.random() * 90000000)}`,
      nric: `${900101}${Math.floor(10 + Math.random() * 90)}${Math.floor(1000 + Math.random() * 9000)}`,
      race: 'Malay',
      date_of_birth: '1990-01-01',
      gender: 'Male',
      occupation: 'Software Engineer',
      emergency_contact_name: 'Emergency Contact',
      emergency_contact_phone: '0123456789',
      emergency_contact_relationship: 'Spouse',
      address: '123 Main Street',
      city: 'Kuala Lumpur',
      state: 'Wilayah Persekutuan',
      postal_code: '50000',
      password: 'password123',
      password_confirmation: 'password123'
    });
    
    if (agentRegResponse.data.status !== 'success') {
      throw new Error(`Agent registration failed: ${JSON.stringify(agentRegResponse.data)}`);
    }
    
    console.log('Agent registered successfully:', agentRegResponse.data.data.user.name);
    const token = agentRegResponse.data.data.access_token;
    
    // 2. Register a medical insurance client
    console.log('2. Registering a medical insurance client...');
    const clientNric = `${910101}${Math.floor(10 + Math.random() * 90)}${Math.floor(1000 + Math.random() * 9000)}`;
    const clientEmail = `test.client.${timestamp}@example.com`;
    
    const registrationResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/register',
      {
        clients: [{
          plan_type: 'medical',
          full_name: `Test Client ${timestamp}`,
          nric: clientNric,
          race: 'Chinese',
          height_cm: 170,
          weight_kg: 65,
          phone_number: `01${Math.floor(10000000 + Math.random() * 90000000)}`,
          email: clientEmail,
          medical_consultation_2_years: false,
          serious_illness_history: false,
          insurance_rejection_history: false,
          serious_injury_history: false,
          emergency_contact_name: 'Emergency Contact',
          emergency_contact_phone: '0123456789',
          emergency_contact_relationship: 'Parent',
          payment_mode: 'monthly',
          medical_card_type: 'standard'
        }]
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    
    if (registrationResponse.data.status !== 'success') {
      throw new Error(`Client registration failed: ${JSON.stringify(registrationResponse.data)}`);
    }
    
    console.log('Client registered successfully:', registrationResponse.data.data.clients[0].full_name);
    const registrationId = registrationResponse.data.data.registration_id;
    const policyIds = registrationResponse.data.data.policies.map(p => p.id);
    
    // 3. Create payment
    console.log('3. Creating payment...');
    const paymentResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/payment',
      {
        registration_id: registrationId,
        policy_ids: policyIds,
        payment_method: 'curlec',
        return_url: 'http://localhost:3000/payment/success',
        cancel_url: 'http://localhost:3000/payment/cancel'
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    
    if (paymentResponse.data.status !== 'success') {
      throw new Error(`Payment creation failed: ${JSON.stringify(paymentResponse.data)}`);
    }
    
    console.log('Payment created successfully:', paymentResponse.data.data.payment.id);
    const paymentId = paymentResponse.data.data.payment.id;
    
    // 4. Simulate payment verification (normally done by webhook)
    console.log('4. Simulating payment verification...');
    const verifyResponse = await axios.post(
      'http://localhost:8000/api/v1/medical-registration/verify',
      {
        payment_id: paymentId,
        status: 'success',
        external_ref: `test_payment_${timestamp}`
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    
    if (verifyResponse.data.status !== 'success') {
      throw new Error(`Payment verification failed: ${JSON.stringify(verifyResponse.data)}`);
    }
    
    console.log('Payment verified successfully');
    
    // 5. Get receipt
    console.log('5. Getting receipt...');
    const receiptResponse = await axios.get(
      `http://localhost:8000/api/v1/medical-registration/receipt/${paymentId}`,
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    
    if (receiptResponse.data.status !== 'success') {
      throw new Error(`Receipt retrieval failed: ${JSON.stringify(receiptResponse.data)}`);
    }
    
    console.log('Receipt retrieved successfully');
    
    // 6. Check agent wallet for commission
    console.log('6. Checking agent wallet for commission...');
    const walletResponse = await axios.get(
      'http://localhost:8000/api/v1/wallet',
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );
    
    if (walletResponse.data.status !== 'success') {
      throw new Error(`Wallet retrieval failed: ${JSON.stringify(walletResponse.data)}`);
    }
    
    console.log('Wallet balance:', walletResponse.data.data.balance);
    console.log('Total earned:', walletResponse.data.data.total_earned);
    
    console.log('\nTest completed successfully!');
  } catch (error) {
    console.error('Test failed:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
    }
  }
}

testCurlecPayment();
