import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../data/models/resident_model.dart';

final studentRepositoryProvider = Provider<StudentRepository>((ref) {
  return StudentRepository(ref.read(apiClientProvider));
});

final studentProfileProvider = FutureProvider<ResidentModel>((ref) async {
  final repository = ref.read(studentRepositoryProvider);
  return repository.getProfile();
});

final electricityHistoryProvider = FutureProvider<List<dynamic>>((ref) async {
  final repository = ref.read(studentRepositoryProvider);
  return repository.getElectricityReadings();
});

final paymentHistoryProvider = FutureProvider<List<dynamic>>((ref) async {
  final repository = ref.read(studentRepositoryProvider);
  return repository.getPayments();
});

final complaintHistoryProvider = FutureProvider<List<dynamic>>((ref) async {
  final repository = ref.read(studentRepositoryProvider);
  return repository.getComplaints();
});

final noticeHistoryProvider = FutureProvider<List<dynamic>>((ref) async {
  final repository = ref.read(studentRepositoryProvider);
  return repository.getNotices();
});

class StudentRepository {
  final Dio _dio;

  StudentRepository(this._dio);

  Future<ResidentModel> getProfile() async {
    try {
      final response = await _dio.get('/student/profile');
      if (response.statusCode == 200) {
        final data = response.data;
        // In a real app we'd map this properly, but for now we map to our existing ResidentModel
        // Or if the backend sends it exactly as we need it, we use factory constructors.
        // For now, let's map the fields manually to fit ResidentModel if needed, 
        // or just let fromJson handle it if the API matches.
        // Assuming the API returns a 'user' or 'student' object
        final studentData = data['student'] ?? data['data'] ?? data;
        
        return ResidentModel(
          id: studentData['id'].toString(),
          fullName: studentData['name'] ?? 'Student',
          phone: studentData['phone'] ?? '',
          email: studentData['email'] ?? '',
          aadhaarNumber: studentData['aadhaar_number'] ?? '',
          panNumber: studentData['pan_number'] ?? '',
          branchName: studentData['branch']?['name'] ?? 'Assigned Branch',
          roomNumber: studentData['bed']?['room']?['room_number'] ?? 'N/A',
          bedCode: studentData['bed']?['bed_code'] ?? 'N/A',
          sharingType: studentData['bed']?['room']?['sharing_type'] ?? 'N/A',
          monthlyRent: double.tryParse(studentData['monthly_rent']?.toString() ?? '0') ?? 0,
          securityDeposit: double.tryParse(studentData['deposit_amount']?.toString() ?? '0') ?? 0,
          joiningDate: studentData['joining_date'] ?? 'N/A',
          rentStatus: studentData['rent_status'] ?? 'Paid',
          depositStatus: studentData['deposit_status'] ?? 'Paid',
          kycStatus: studentData['kyc_status'] ?? 'Verified',
        );
      }
      throw Exception('Failed to load profile');
    } catch (e) {
      throw Exception('Error fetching profile: $e');
    }
  }

  Future<List<dynamic>> getElectricityReadings() async {
    try {
      final response = await _dio.get('/student/electricity-readings');
      return response.data['data'] ?? [];
    } catch (e) {
      throw Exception('Error fetching electricity readings: $e');
    }
  }

  Future<void> submitElectricityReading(FormData formData) async {
    try {
      await _dio.post('/student/electricity-reading', data: formData);
    } catch (e) {
      throw Exception('Error submitting reading: $e');
    }
  }

  Future<List<dynamic>> getPayments() async {
    try {
      final response = await _dio.get('/student/payments');
      return response.data['data'] ?? [];
    } catch (e) {
      throw Exception('Error fetching payments: $e');
    }
  }

  Future<void> submitPaymentProof(FormData formData) async {
    try {
      await _dio.post('/student/payment-proof', data: formData);
    } catch (e) {
      throw Exception('Error submitting payment proof: $e');
    }
  }

  Future<List<dynamic>> getComplaints() async {
    try {
      final response = await _dio.get('/student/complaints');
      return response.data['data'] ?? [];
    } catch (e) {
      throw Exception('Error fetching complaints: $e');
    }
  }

  Future<void> createComplaint(Map<String, dynamic> data) async {
    try {
      await _dio.post('/student/complaint', data: data);
    } catch (e) {
      throw Exception('Error creating complaint: $e');
    }
  }

  Future<List<dynamic>> getNotices() async {
    try {
      final response = await _dio.get('/student/notices');
      return response.data['data'] ?? [];
    } catch (e) {
      throw Exception('Error fetching notices: $e');
    }
  }

  Future<void> register(FormData formData) async {
    try {
      final response = await _dio.post('/student/register', data: formData);
      if (response.statusCode != 201) {
        throw Exception('Failed to register');
      }
    } catch (e) {
      throw Exception('Error registering student: $e');
    }
  }
}
