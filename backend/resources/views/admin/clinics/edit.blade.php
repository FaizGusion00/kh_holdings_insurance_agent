@extends('admin.layouts.app')

@section('title', 'Edit Clinic')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Clinic</h1>
            <p class="mt-2 text-sm text-gray-700">Update clinic information and settings.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.clinics.index') }}" class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Clinics
            </a>
        </div>
    </div>

    <div class="mt-8">
        <form action="{{ route('admin.clinics.update', $clinic->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Basic Information -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Clinic Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $clinic->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description', $clinic->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address Information -->
                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="address" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('address', $clinic->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $clinic->city) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                            <select id="state" name="state" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select State</option>
                                <option value="Johor" {{ old('state', $clinic->state) == 'Johor' ? 'selected' : '' }}>Johor</option>
                                <option value="Kedah" {{ old('state', $clinic->state) == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                                <option value="Kelantan" {{ old('state', $clinic->state) == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                                <option value="Kuala Lumpur" {{ old('state', $clinic->state) == 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
                                <option value="Labuan" {{ old('state', $clinic->state) == 'Labuan' ? 'selected' : '' }}>Labuan</option>
                                <option value="Melaka" {{ old('state', $clinic->state) == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                                <option value="Negeri Sembilan" {{ old('state', $clinic->state) == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                                <option value="Pahang" {{ old('state', $clinic->state) == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                                <option value="Perak" {{ old('state', $clinic->state) == 'Perak' ? 'selected' : '' }}>Perak</option>
                                <option value="Perlis" {{ old('state', $clinic->state) == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                                <option value="Pulau Pinang" {{ old('state', $clinic->state) == 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
                                <option value="Putrajaya" {{ old('state', $clinic->state) == 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
                                <option value="Sabah" {{ old('state', $clinic->state) == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                                <option value="Sarawak" {{ old('state', $clinic->state) == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                                <option value="Selangor" {{ old('state', $clinic->state) == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                                <option value="Terengganu" {{ old('state', $clinic->state) == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                            </select>
                            @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $clinic->postal_code) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('postal_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                            <input type="text" name="country" id="country" value="{{ old('country', $clinic->country) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $clinic->phone) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $clinic->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                            <input type="url" name="website" id="website" value="{{ old('website', $clinic->website) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('website')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- License Information -->
                        <div>
                            <label for="license_number" class="block text-sm font-medium text-gray-700">License Number</label>
                            <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $clinic->license_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('license_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="license_expiry" class="block text-sm font-medium text-gray-700">License Expiry Date</label>
                            <input type="date" name="license_expiry" id="license_expiry" value="{{ old('license_expiry', $clinic->license_expiry && is_object($clinic->license_expiry) ? $clinic->license_expiry->format('Y-m-d') : $clinic->license_expiry) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('license_expiry')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Specialties -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Specialties</label>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                @php
                                    $currentSpecialties = is_string($clinic->specialties) ? 
                                        json_decode($clinic->specialties, true) : 
                                        $clinic->specialties;
                                    $currentSpecialties = is_array($currentSpecialties) ? $currentSpecialties : [];
                                @endphp
                                
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="General Practice" id="general_practice" 
                                           {{ in_array('General Practice', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="general_practice" class="ml-2 text-sm text-gray-700">General Practice</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Family Medicine" id="family_medicine" 
                                           {{ in_array('Family Medicine', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="family_medicine" class="ml-2 text-sm text-gray-700">Family Medicine</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Pediatrics" id="pediatrics" 
                                           {{ in_array('Pediatrics', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="pediatrics" class="ml-2 text-sm text-gray-700">Pediatrics</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Obstetrics" id="obstetrics" 
                                           {{ in_array('Obstetrics', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="obstetrics" class="ml-2 text-sm text-gray-700">Obstetrics</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Gynecology" id="gynecology" 
                                           {{ in_array('Gynecology', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="gynecology" class="ml-2 text-sm text-gray-700">Gynecology</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Dermatology" id="dermatology" 
                                           {{ in_array('Dermatology', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="dermatology" class="ml-2 text-sm text-gray-700">Dermatology</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Ophthalmology" id="ophthalmology" 
                                           {{ in_array('Ophthalmology', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="ophthalmology" class="ml-2 text-sm text-gray-700">Ophthalmology</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Dental" id="dental" 
                                           {{ in_array('Dental', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="dental" class="ml-2 text-sm text-gray-700">Dental</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Physiotherapy" id="physiotherapy" 
                                           {{ in_array('Physiotherapy', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="physiotherapy" class="ml-2 text-sm text-gray-700">Physiotherapy</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Laboratory" id="laboratory" 
                                           {{ in_array('Laboratory', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="laboratory" class="ml-2 text-sm text-gray-700">Laboratory</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Radiology" id="radiology" 
                                           {{ in_array('Radiology', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="radiology" class="ml-2 text-sm text-gray-700">Radiology</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="specialties[]" value="Pharmacy" id="pharmacy" 
                                           {{ in_array('Pharmacy', $currentSpecialties) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="pharmacy" class="ml-2 text-sm text-gray-700">Pharmacy</label>
                                </div>
                            </div>
                            @error('specialties')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Operating Hours -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Operating Hours</label>
                            <div class="space-y-3">
                                @php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    $currentOperatingHours = is_string($clinic->operating_hours) ? 
                                        json_decode($clinic->operating_hours, true) : 
                                        $clinic->operating_hours;
                                    $currentOperatingHours = is_array($currentOperatingHours) ? $currentOperatingHours : [];
                                @endphp
                                @foreach($days as $day)
                                <div class="flex items-center space-x-4">
                                    <div class="w-24">
                                        <span class="text-sm font-medium text-gray-700">{{ $day }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $dayData = $currentOperatingHours[$day] ?? null;
                                            $openTime = $dayData['open_time'] ?? '';
                                            $closeTime = $dayData['close_time'] ?? '';
                                            $isClosed = isset($dayData['closed']) && $dayData['closed'];
                                        @endphp
                                        <input type="time" name="operating_hours[{{ $day }}][open_time]" 
                                               value="{{ $openTime }}" 
                                               {{ $isClosed ? 'disabled' : '' }}
                                               class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <span class="text-sm text-gray-500">to</span>
                                        <input type="time" name="operating_hours[{{ $day }}][close_time]" 
                                               value="{{ $closeTime }}" 
                                               {{ $isClosed ? 'disabled' : '' }}
                                               class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="operating_hours[{{ $day }}][closed]" 
                                               id="closed_{{ strtolower($day) }}" 
                                               {{ $isClosed ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="closed_{{ strtolower($day) }}" class="ml-2 text-sm text-gray-500">Closed</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('operating_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="is_active" name="is_active" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="1" {{ old('is_active', $clinic->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $clinic->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.clinics.index') }}" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Update Clinic
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle closed checkbox logic
    const closedCheckboxes = document.querySelectorAll('input[name*="[closed]"]');
    closedCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const timeInputs = this.closest('div').querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.disabled = this.checked;
                if (this.checked) {
                    input.value = '';
                }
            });
        });
    });
});
</script>
@endsection
