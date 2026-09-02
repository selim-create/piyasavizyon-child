# Piyasa Vizyon Child Theme

Piyasa Vizyon'un aktif WordPress child theme reposudur. Ana hedef, legacy **BirFinans** parent theme bağımlılıklarını kontrollü olarak kaldırmak ve tema tamamen standalone hale geldikten sonra production PHP sürümünü 7.4'ten 8.3'e taşımaktır.

## Güncel çalışma prensibi

- Production şu anda PHP 7.4 üzerinde kalır.
- `Template: birfinans` final standalone cutover yapılana kadar kaldırılmaz.
- Parent tema klasörü final geçişe kadar production'da tutulur.
- Aktif runtime bağımlılıkları küçük ve doğrulanabilir PR'larla child-owned yapılır.
- Her PR production'da ayrı syntax, parser/data ve browser smoke testlerinden geçirilir.
- Auth/member bağımlılıkları finans veri katmanından ayrı fazda ele alınır.

## Tamamlanan ana market fazları

- Döviz / Altın / Parite / Coin child cache ve payload contract altyapısı
- Canlı child-owned döviz, altın ve parite kaynak akışları
- Borsa, endeks ve hisse list/detail akışlarının önemli bölümü
- Parite liste + detay parent API/runtime bağımlılığından çıkarıldı
- Altın liste + detay parent API/runtime bağımlılığından çıkarıldı
- Altın grafik kaynağı Mynet `initChartData` ile child-owned hale getirildi
- Döviz Serbest Piyasa detay market katmanı child-owned hale getirildi; banka-spesifik/member parçalarının migrasyonu devam ediyor

## Devam eden bağımsızlaşma sırası

1. Döviz detay / banka kaynaklarının tamamlanması
2. Döviz tablo ve döviz arşiv parent bağımlılıkları
3. Faiz ve kalan market API bağımlılıkları
4. Coin detayındaki kalan parent asset/options kullanımları
5. `$bp_options` genel temizliği
6. Login/member `ajaxlogin` ve `user_api.php` bağımlılıkları
7. Parent template part / asset fallback'lerinin kaldırılması
8. `Template: birfinans` kaldırılması
9. İzole PHP 8.3 testleri
10. Production PHP 8.3 geçişi

## Teknik audit

Güncel BirFinans dependency envanteri ve teknik notlar:

- [`docs/BIRFINANS-DEPENDENCY-AUDIT.md`](docs/BIRFINANS-DEPENDENCY-AUDIT.md)

Bu audit aktif runtime ile yalnızca repoda duran legacy/static referansları ayrı değerlendirmek için kullanılır.

## CSS / frontend konsolidasyonu

Tema geçmiş revizyonlardan kalan büyük ve parçalı bir CSS yapısına sahiptir. CSS dosyaları doğrudan silinmeyecek; önce gerçek enqueue zinciri ve selector çakışmaları çıkarılacaktır.

Şu an aktif zincirde başlıca:

- `assets/css/pv-v7.css`
- `assets/css/pv-footer-v250.css`
- `assets/css/pv-corporate-v252.css`
- `assets/css/pv-header-v260.css`

Hedef, parent bağımsızlaşma sırasında layout regresyonu yaratmadan bunları daha az ve daha anlamlı stylesheet altında konsolide etmektir. Kullanılmayan eski CSS dosyaları envanter doğrulamasından sonra silinecektir.

## Dağıtım notu

Bu repo production'a manuel yüklenmektedir. PR merge/deploy doğrulaması yapılmadan production test fazına geçilmez.
