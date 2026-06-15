@php($doctor = $doctor ?? null)
<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Doctor Name <span class="text-red-500">*</span></label>
            <input name="doctor_name" value="{{ old('doctor_name', $doctor?->name) }}" required maxlength="191"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('doctor_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Speciality <span class="text-red-500">*</span></label>
            <input name="speciality" value="{{ old('speciality', $doctor?->meta_data['speciality'] ?? null) }}" required maxlength="191"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('speciality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Clinic Name</label>
            <input name="clinic_name" value="{{ old('clinic_name', $doctor?->hospital_name) }}" maxlength="191"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('clinic_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Clinic Mobile</label>
            <input name="clinic_mobile" value="{{ old('clinic_mobile', $doctor?->personal_contact_no) }}" maxlength="20"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('clinic_mobile')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
            <input name="city" value="{{ old('city', $doctor?->meta_data['city_name'] ?? $doctor?->city?->name) }}" maxlength="191"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
            <input name="location" value="{{ old('location', $doctor?->meta_data['location_name'] ?? $doctor?->location?->location) }}" maxlength="191"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('location')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Clinic Address <span class="text-red-500">*</span></label>
            <textarea name="clinic_address" rows="3" required maxlength="500"
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition resize-none">{{ old('clinic_address', $doctor?->address) }}</textarea>
            @error('clinic_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Google Business URL</label>
            <input name="google_business_url" value="{{ old('google_business_url', $doctor?->external_url) }}" maxlength="2000"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('google_business_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            @php($status = old('status', $doctor && ! $doctor->status ? 'inactive' : 'active'))
            <select name="status"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
    </div>
</div>
