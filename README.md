# Restoran Kod Meda
Kako pokrenuti projekat na računaru

1. **Prebacivanje fajlova:**
   Preuzmite ceo folder sa GitHub-a i ubacite ga u vaš lokalni server:
   * Ako koristite **XAMPP**: ubacite u `C:\xampp\htdocs\`
  
2. **Pravljenje i uvoz baze podataka:**
   * Upalite XAMPP (Apache i MySQL).
   * Idite na `http://localhost/phpmyadmin/`.
   * Napravite novu bazu i nazovite je tačno: **`restoran_medo`**.
   * Kliknite na tu bazu, idite na opciju **Import** (Uvoz) i izaberite fajl **`restoran_medo.sql`** koji se nalazi u ovom mom folderu.

3. **Pokretanje sajta:**
   Kada se baza uveze, u pretraživaču samo otvorite adresu foldera, na primer:
   `http://localhost/restoran-medo/`
   
## Podaci za konekciju (fajl `konekcija.php`)
Konekcija je podešena na klasične parametre za localhost, tako da bi trebalo da proradi odmah:
* **Host:** localhost
* **User:** root
* **Password:** (prazno)
* **Baza:** restoran_medo
