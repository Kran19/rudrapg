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
        final studentData = data['student'] ?? data['data'] ?? data;
        
        return ResidentModel(
          id: studentData['id']?.toString() ?? '',
          fullName: studentData['full_name'] ?? studentData['name'] ?? '',
          phone: studentData['phone'] ?? '',
          email: studentData['email'] ?? '',
          aadhaarNumber: studentData['aadhaar_number'] ?? '',
          panNumber: studentData['pan_number'] ?? '',
          parentName: studentData['parent_name'] ?? '',
          parentPhone: studentData['parent_phone'] ?? '',
          emergencyContact: studentData['emergency_contact'] ?? studentData['parent_phone'] ?? '',
          currentAddress: studentData['current_address'] ?? '',
          branchId: studentData['branch']?['id']?.toString() ?? '',
          branchName: studentData['branch']?['name'] ?? 'N/A',
          floorNumber: int.tryParse(studentData['room']?['floor_number']?.toString() ?? '1') ?? 1,
          roomNumber: studentData['room']?['room_number'] ?? studentData['bed']?['room']?['room_number'] ?? 'N/A',
          bedCode: studentData['bed']?['bed_code'] ?? 'N/A',
          sharingType: studentData['room']?['sharing_type'] ?? 'N/A',
          monthlyRent: double.tryParse((studentData['bed']?['monthly_rent'] ?? studentData['monthly_rent'])?.toString() ?? '0') ?? 0,
          securityDeposit: double.tryParse((studentData['bed']?['security_deposit'] ?? studentData['deposit_amount'])?.toString() ?? '0') ?? 0,
          joiningDate: studentData['joining_date'] ?? 'N/A',
          rentStatus: studentData['rent_status'] ?? 'N/A',
          depositStatus: studentData['deposit_status'] ?? 'N/A',
          kycStatus: studentData['kyc_status'] ?? 'N/A',
          status: studentData['status'] ?? 'PENDING_APPROVAL',
          emergencyContactName: studentData['parent_name'] ?? '',
          emergencyContactPhone: studentData['emergency_contact'] ?? studentData['parent_phone'] ?? '',
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
      await _dio.post(
        '/student/electricity-reading',
        data: formData,
        options: Options(contentType: null, headers: {'Accept': 'application/json'}),
      );
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
      await _dio.post(
        '/student/payment-proof',
        data: formData,
        options: Options(contentType: null, headers: {'Accept': 'application/json'}),
      );
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

  Future<String> register(FormData formData) async {
    try {
      final response = await _dio.post(
        '/student/register',
        data: formData,
        options: Options(contentType: null, headers: {'Accept': 'application/json'}),
      );
      if (response.statusCode != 201) {
        throw Exception('Failed to register');
      }
      return response.data['data']['app_reference'] ?? 'REG-UNKNOWN';
    } catch (e) {
      if (e is DioException) {
        rethrow;
      }
      throw Exception('Error registering student: $e');
    }
  }
}
