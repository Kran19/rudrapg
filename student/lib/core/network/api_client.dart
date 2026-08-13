import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// Provide the SharedPreferences instance
final sharedPreferencesProvider = Provider<SharedPreferences>((ref) {
  throw UnimplementedError();
});

// Provide the Dio API client
final apiClientProvider = Provider<Dio>((ref) {
  final prefs = ref.read(sharedPreferencesProvider);
  
  // Set this to your live server URL so the mobile app can connect to it.
  // Use 'http://127.0.0.1:8000/api/v1' only if testing locally on Web/Desktop.
  String baseUrl = 'https://emperorsmartsolutions.com/rudrapgwebsite/api/v1';
  
  // NOTE: If you are running locally and want to test on the emulator, uncomment this block:
  /*
  baseUrl = 'http://127.0.0.1:8000/api/v1';
  try {
    if (Platform.isAndroid) {
      baseUrl = 'http://10.0.2.2:8000/api/v1';
    }
  } catch (e) {
    // Platform.isAndroid throws on web
  }
  */

  final dio = Dio(BaseOptions(
    baseUrl: baseUrl,
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 10),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  ));

  // Add Auth Interceptor
  dio.interceptors.add(InterceptorsWrapper(
    onRequest: (options, handler) {
      final token = prefs.getString('auth_token');
      if (token != null && token.isNotEmpty) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      return handler.next(options);
    },
    onError: (DioException e, handler) {
      // Could handle 401 Unauthorized globally here
      return handler.next(e);
    }
  ));

  // Logging Interceptor for easier debugging
  dio.interceptors.add(LogInterceptor(
    requestBody: true,
    responseBody: true,
  ));

  return dio;
});
