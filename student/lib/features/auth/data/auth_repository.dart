import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import 'package:shared_preferences/shared_preferences.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    ref.read(apiClientProvider),
    ref.read(sharedPreferencesProvider),
  );
});

class AuthRepository {
  final Dio _dio;
  final SharedPreferences _prefs;

  AuthRepository(this._dio, this._prefs);

  Future<void> login(String phone, String password) async {
    try {
      final response = await _dio.post('/auth/login', data: {
        'phone': phone,
        'password': password,
      });

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['data'] != null && data['data']['token'] != null) {
          await _prefs.setString('auth_token', data['data']['token']);
          // Could also save user data to prefs if needed
        }
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 422) {
        throw Exception(e.response?.data['message'] ?? 'Validation Error');
      } else if (e.response?.statusCode == 401) {
        throw Exception('Invalid credentials');
      }
      throw Exception('Failed to connect to server');
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } catch (e) {
      // Ignore errors on logout, just clear local state
    } finally {
      await _prefs.remove('auth_token');
    }
  }

  Future<String> requestPasswordReset(String phone, {String? notes}) async {
    try {
      final response = await _dio.post('/auth/forgot-password-request', data: {
        'phone': phone,
        if (notes != null && notes.isNotEmpty) 'notes': notes,
      });

      if (response.statusCode == 200 || response.statusCode == 201) {
        return response.data['message'] ?? 'Password reset request submitted successfully.';
      }
      throw Exception(response.data['message'] ?? 'Failed to submit password reset request.');
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Failed to connect to server. Please try again.');
    }
  }
}
