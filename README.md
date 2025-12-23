# ProxPortal

ProxPortal è un **portale di provisioning automatico di container LXC su Proxmox VE**, pensato per gestire richieste utenti, approvazioni amministrative e creazione sicura di container con **chiavi SSH uniche per ogni utente**.

Il progetto nasce come **pannello di accesso semplificato** sopra Proxmox, con logica di approvazione, profili di risorse e integrazione diretta via API.

---

## Funzionalità principali

- ✅ Autenticazione utenti (admin / user)
- ✅ Richiesta di container LXC
- ✅ Approvazione o rifiuto da pannello admin
- ✅ Creazione automatica container LXC su Proxmox
- ✅ Generazione **chiave SSH unica per ogni container**
- ✅ Download della chiave privata (una sola volta)
- ✅ Profili di risorse predefiniti (Bronze / Silver / Gold)
- ✅ Interfaccia moderna (Laravel + Tailwind)

---

## Requisiti

- PHP >= 8.2
- Composer
- Node.js + npm
- MySQL / MariaDB
- Proxmox VE (testato con Proxmox 9 / API v2)
- Accesso API Proxmox (utente `root@pam` o dedicato)

---

## Installazione

### 1. Clona il repository
```bash
git clone https://github.com/AlanGregorio23/ProxPortal
cd ProxPortal
````

### 2. Installa le dipendenze

```bash
composer install
npm install
npm run build
```

### 3. Configura l’ambiente

Copia il file di esempio:

```bash
cp .env.example .env
```

Genera la chiave Laravel:

```bash
php artisan key:generate
```

---

## Configurazione `.env`

Le variabili principali da configurare:

```env
# Database
DB_DATABASE=proxportal
DB_USERNAME=root
DB_PASSWORD=

# Proxmox
PROXMOX_NODE=px1
PROXMOX_HOST=192.168.56.15
PROXMOX_PORT=8006
PROXMOX_SCHEME=https
PROXMOX_USER=root
PROXMOX_PASSWORD=********
PROXMOX_REALM=pam

PROXMOX_TEMPLATE=iso:vztmpl/ubuntu-22.04-standard_22.04-1_amd64.tar.zst
PROXMOX_STORAGE=zpool
PROXMOX_BRIDGE=vmbr0
PROXMOX_LXC_USER=root
PROXMOX_NAMESERVER=8.8.8.8
```

---

## Migrazioni e dati iniziali

```bash
php artisan migrate
php artisan db:seed
```

### Utenti di default

| Ruolo | Email                                               | Password |
| ----- | --------------------------------------------------- | -------- |
| Admin | [admin@proxportal.com](mailto:admin@proxportal.com) | admin123 |
| User  | [user@proxportal.com](mailto:user@proxportal.com)   | user123  |

---

## Avvio del progetto

```bash
php artisan serve
```

Apri il browser su:

```
http://localhost:8000
```

---

## Come funziona ProxPortal

### 1. Flusso utente

1. L’utente effettua il login
2. Invia una richiesta di container scegliendo un profilo
3. La richiesta entra in stato `pending`
4. Attende approvazione dell’amministratore

### 2. Flusso admin

1. L’admin vede tutte le richieste
2. Può **approvare o rifiutare**
3. All’approvazione:

   * viene creato un container LXC su Proxmox
   * viene generata una **chiave SSH unica**
   * il container viene avviato
4. L’utente può scaricare la **chiave privata SSH**

---

## Integrazione con Proxmox

ProxPortal comunica con Proxmox tramite:

* **API REST ufficiali** (`/api2/json`)
* Autenticazione con ticket + CSRF token
* Endpoint LXC:

  * Creazione container:

    ```
    POST /nodes/{node}/lxc
    ```
  * Lettura interfacce di rete:

    ```
    GET /nodes/{node}/lxc/{vmid}/interfaces
    ```

---

## Profili di risorse

I container vengono creati secondo profili predefiniti:

### 🟫 Bronze

* 1 core CPU
* 384 MB RAM
* 3 GB disco
* CPU units: 512

### 🥈 Silver

* 1 core CPU
* 768 MB RAM
* 5 GB disco
* CPU units: 1024

### 🥇 Gold

* 1 core CPU
* 1200 MB RAM
* 8 GB disco
* CPU units: 1400

Le risorse vengono impostate **dinamicamente via API**, indipendenti dal template.

---

## Sicurezza

* 🔐 Ogni container ha una **chiave SSH diversa**
* 🔐 Nessuna password SSH condivisa
* 🔐 Accesso Proxmox isolato nel backend
* 🔐 Nessuna esecuzione di comandi remoti via API

---

## Limitazioni note

* La replica LXC **non è disponibile via API Proxmox**
* Snapshot e backup non sono gestiti dal portale
* Replication richiede configurazione manuale lato Proxmox

---

