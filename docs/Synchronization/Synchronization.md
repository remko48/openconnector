# Synchronization

Synchronization is a core feature that enables data transfer between different systems and APIs. Here's how it works:

## Overview
- Synchronizations define how data should be synchronized between a source and target system
- Each synchronization has a source configuration (where to get data from) and target configuration (where to send data to)
- The synchronization process handles pagination, data mapping, and maintaining sync state

## Key Components

### Source Configuration
- Defines where to fetch data from (API endpoint, database etc)
- Specifies how to locate objects in the response using resultsPosition:
  - `_root`: Uses the entire response body as the objects array
  - Dot notation (e.g. `data.items`): Extracts objects from a nested path
  - Common keys (`items`, `result`, `results`): Automatically checks these standard locations
  - Custom path: Specify any JSON path to locate the objects
- Configures pagination settings (paginationQuery)
- Can include conditions to filter which objects to sync

### Target Configuration 
- Defines where to send synchronized data
- Specifies how to map source data to target format
- Handles create/update/delete operations

### Synchronization Contracts
- Tracks the sync state for each individual object
- Stores origin ID and target ID mappings
- Maintains hashes to detect changes
- Logs synchronization events and errors

## Process Flow
1. Fetch objects from source system with pagination
2. Filter objects based on configured conditions
3. Create/update synchronization contracts for each object
4. Transform data according to mapping rules
5. Write objects to target system (POST/PUT/DELETE)
6. Update contract status and hashes
7. Handle any follow-up synchronizations

## Error Handling
- Rate limiting detection and backoff
- Logging of failed operations
- Contract state tracking for retry attempts

The synchronization system provides a robust way to keep data in sync across different systems while maintaining state and handling errors gracefully.

## Results Position Examples

### Root Level Response
For an API that returns an array directly:
