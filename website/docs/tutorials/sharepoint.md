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

Let's walk through how OpenConnector can automatically publish WOO documents from SharePoint to Open Catalogi:

1. **SharePoint Connection Setup**
   - Configure OpenConnector with your SharePoint endpoint URL
   - Set up certificate-based authentication using your organization's certificates
   - Establish secure connection parameters

2. **Folder Structure Navigation**
   - OpenConnector systematically traverses through SharePoint folders
   - Identifies folders containing WOO documents
   - Reads metadata from folder labels and properties

3. **Publication Creation in Open Register**
   - For each SharePoint folder:
     - Creates a new publication object in Open Register
     - Maps SharePoint folder metadata to publication properties
     - Sets appropriate WOO categories and classifications
     - Establishes publication dates and visibility settings

4. **Document Synchronization**
   - Automatically syncs all documents from SharePoint folders
   - Maintains file integrity and metadata
   - Updates documents when source files change
   - Preserves version history and audit trail

This automated flow ensures that WOO documents are consistently and accurately published while maintaining security and compliance requirements.
