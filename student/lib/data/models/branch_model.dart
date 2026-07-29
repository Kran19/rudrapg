class BranchModel {
  final String id;
  final String code;
  final String name;
  final String address;
  final String city;
  final String contactNumber;
  final String managerName;
  final String managerPhone;
  final String imageUrl;
  final List<String> galleryImages;
  final List<String> amenities;
  final List<String> rules;

  BranchModel({
    required this.id,
    required this.code,
    required this.name,
    required this.address,
    required this.city,
    required this.contactNumber,
    required this.managerName,
    required this.managerPhone,
    required this.imageUrl,
    required this.galleryImages,
    required this.amenities,
    required this.rules,
  });
}
