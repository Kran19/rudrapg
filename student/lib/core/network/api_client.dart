import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/foundation.dart';

// Provide the SharedPreferences instance
final sharedPreferencesProvider = Provider<SharedPreferences>((ref) {
  throw UnimplementedError();
});

const String _prodBaseUrl = 'https://emperorsmartsolutions.com/rudrapgwebsite/api/v1';
const String _localBaseUrl = 'http://127.0.0.1:8000/api/v1';
const String _emulatorBaseUrl = 'http://10.0.2.2:8000/api/v1';

// Provide the Dio API client
final apiClientProvider = Provider<Dio>((ref) {
  final prefs = ref.read(sharedPreferencesProvider);
  
  // Production-Ready URL Resolution:
  // 1. Default to the production server.
  String baseUrl = _prodBaseUrl;
  
  // 2. In debug mode, default to local development.
  if (kDebugMode) {
    if (kIsWeb) {
      baseUrl = _localBaseUrl;
    } else if (defaultTargetPlatform == TargetPlatform.android) {
      // Android emulator maps 10.0.2.2 to host's localhost
      baseUrl = _emulatorBaseUrl;
    } else {
      baseUrl = _localBaseUrl;
    }
  }
  
  // 3. Allow build-time override via --dart-define=API_URL=...
  const envBaseUrl = String.fromEnvironment('API_URL');
  if (envBaseUrl.isNotEmpty) {
    baseUrl = envBaseUrl;
  }

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
