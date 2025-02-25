---
sidebar_position: 1
title: SharePoint Integration
description: Learn how to integrate SharePoint with OpenConnector for WOO document publication
tags:
    - sharepoint
    - woo
    - tutorial
    - integration
---

# SharePoint Integration with OpenConnector

SharePoint is Microsoft's document management and collaboration platform that allows organizations to store, organize, share, and access information securely. Many organizations, especially government institutions, use SharePoint to manage their internal documents and workflows.

## Why Connect to SharePoint?

In the context of Open Data and specifically the Dutch WOO (Wet Open Overheid - Open Government Act), organizations need to make certain documents publicly accessible. However, these documents often reside in internal SharePoint environments. OpenConnector provides a seamless solution to bridge this gap.

## Example: WOO Documents Publication Flow

OpenConnector automates the publication of WOO documents from SharePoint to Open Catalogi through the following steps:

1. **Configuring SharePoint Access (Microsoft Entra Setup)**
	- Register an application in Microsoft Entra.
	- Assign API permissions for SharePoint.
	- Upload and configure certificate-based authentication.

2. **Establishing a Connection in OpenConnector**
	- Set up SharePoint as a source in OpenConnector.
	- Define authentication settings and API endpoints.
	- Validate connection through test requests.

3. **Navigating the Folder Structure in SharePoint**
	- OpenConnector scans designated SharePoint folders for WOO documents.
	- Reads metadata and identifies publication-ready content.

4. **Publishing to Open Register**
	- Maps SharePoint metadata to Open Register properties.
	- Assigns WOO classifications, publication dates, and visibility settings.

5. **Synchronizing Documents**
	- Automatically syncs SharePoint documents with OpenCatalogi.
	- Maintains version history and updates files upon changes.

This automated workflow ensures consistent, secure, and compliant publication of WOO documents.

## Setting Up SharePoint Authentication

### Prerequisites
- A Microsoft SharePoint instance managed with Microsoft Entra.
- A Nextcloud instance with OpenConnector installed.
- A valid X.509 certificate and the private key.
- A Linux command line (or Git Bash on Windows) for generating values later in the process.

### Configuring Microsoft Entra

#### Step 1: Register an Application
1. Navigate to **Identity → Applications → App registrations**.
2. Create a new application and assign a name.

   ![Entra App Registration](images/Entra_AppRegistration.png)

#### Step 2: Assign SharePoint API Permissions
1. Under the **API Permissions** tab, click ‘Add permissions’.

   ![Entra Add Permission 1](images/Entra_AddPermission_1.png)

   ![Entra Add Permission 2](images/Entra_AddPermission_2.png)

2. Click **Delegated permissions** and enable:
	- `AllSites.Read`

   ![Entra Add Permission 3](images/Entra_AddPermission_3.png)

3. Select **SharePoint → Application permissions** and enable:
	- `Sites.Read.All`

   ![Entra Add Permission 4](images/Entra_AddPermission_4.png)

4. Click **Grant admin consent** to finalize.

   ![Entra Grant Consent](images/Entra_GrantConsent.png)

#### Step 3: Upload Certificate for Authentication
1. Go to the **Certificates & secrets** tab.
2. Under **Certificates**, click **Upload certificate**.
3. Upload the public certificate (`.crt` or `.pem`).

   ![Entra Upload Certificate](images/Entra_UploadCertificate.png)

4. Save the certificate thumbprint for later use.

   ![Entra Certificate Thumbprint](images/Entra_CertificateThumbprint.png)

### Configuring OpenConnector as a SharePoint Source

#### Step 1: Add a Source in OpenConnector
1. Open Nextcloud and navigate to OpenConnector.
2. Click **Sources → Add source**.
3. Provide the name, description, and SharePoint URL (e.g., `https://{tenant}.sharepoint.com/_api`).
4. Set **Type** to API.

   ![Add Source](images/OpenConnector_AddSource.png)

#### Step 2: Set Up Authentication
1. Navigate to the **Authentication** tab and click **Add Authentication**.
2. Enter the following key-value pairs (italic values can be copied without editing):

| Key                   | Value                                                                                                                                                                                                                                | Note                                                                                                                |
|-----------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------|
| grant_type            | *client_credentials*                                                                                                                                                                                                                 |                                                                                                                     |
| scope                 | `https://{sharepoint_url}/.default`                                                                                                                                                                                                  | See above for the default URL                                                                                       |
| authentication        | *body*                                                                                                                                                                                                                               |                                                                                                                     |
| client_id             | (The client ID of the app registration)                                                                                                                                                                                              | See ‘Obtain client and tenant ID’                                                                                   |
| client_secret         | (Empty string)                                                                                                                                                                                                                       | Due to OAuth boundaries, this is a mandatory key but should be left empty                                           |
| client_assertion_type | *urn:ietf:params:oauth:client-assertion-type:jwt-bearer*                                                                                                                                                                             |                                                                                                                     |
| private_key           | (Base64 encoded private key of the certificate)                                                                                                                                                                                      | Highly recommended to use a secret manager for storing the private key                                              |
| x5t                   | (SHA1 thumbprint of the certificate)                                                                                                                                                                                                 | See ‘Obtain x5t value’                                                                                              |
| payload               | `{"aud": "{tokenUrl}","exp": {{ 'now'\|date_modify('+15 minutes')\|date('U') }},"iss": "{client_id}","jti":"dfdaa67d-d76e-48c4-a349-58861983869e","nbf": {{ 'now'\|date('U') }},"sub": "{client_id}","iat": {{ 'now'\|date('U') }}}` | Replace `{tokenUrl}` with the same value as ‘tokenUrl’ (see below) and `{client_id}` with the client_id (see above) |
| tokenUrl              | `https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/token`                                                                                                                                                                    | Replace `{tenant_id}` with your Microsoft Tenant ID (see ‘Obtain client and tenant ID’)                             |

#### Step 3: Configure Headers
1. In **Configurations**, add:
	- `headers.Authorization` → `Bearer {{ oauthToken(source) }}`
	- `headers.Accept` → `application/json;odata=verbose`

#### Step 4: Test the Source
1. Click **Test** and enter the endpoint `/Web/lists`.
2. A successful response (`Status:

## Configuring SharePoint for WOO Document Storage

To enable WOO publications, SharePoint must be structured appropriately.

### Setting Up a Publication Folder

Each publication is stored as a separate folder inside a designated parent directory.

Example API endpoint for retrieving publications: `/_api/Web/GetFolderByServerRelativePath(decodedurl='/WOO/Woo-verzoeken en -besluiten')/folders`


**Folder structure:**

![Category Folder Structure](images/overzicht-categorie-folders.png)

Each folder within the parent directory represents a single publication:

![Publication Example](images/overzicht-woo-verzoeken.png)

### Configuring Metadata in SharePoint

To ensure compliance with OpenWOO, metadata fields must be configured in SharePoint.

1. Navigate to **Library settings** → **More settings**.
2. Enable **Enterprise Metadata and Keywords**.
3. Create metadata columns matching OpenWOO fields.

![Enable Metadata](images/turn-on-metadata.png)

#### Updating Metadata
1. Open the document library and locate the publication folder.
2. Click **Grid edit** to modify metadata.
3. Save changes by clicking **Exit grid**.

![Edit Grid](images/edit-grid.png)

By correctly setting up SharePoint metadata, OpenConnector can efficiently process and publish WOO documents.
