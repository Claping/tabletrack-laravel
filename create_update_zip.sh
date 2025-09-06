#!/bin/bash

# Script to create a zip file with changes between the last 2 git tags
# Usage: ./create_update_zip.sh

set -e  # Exit on any error

# Function to print colored output
print_info() {
    echo -e "\033[1;34m[INFO]\033[0m $1"
}

print_success() {
    echo -e "\033[1;32m[SUCCESS]\033[0m $1"
}

print_error() {
    echo -e "\033[1;31m[ERROR]\033[0m $1"
}

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    print_error "Not in a git repository!"
    exit 1
fi

# Get the last 2 tags (sorted by version)
print_info "Getting the last 2 git tags..."
tags=($(git tag --sort=-version:refname | head -2))

if [ ${#tags[@]} -lt 2 ]; then
    print_error "Need at least 2 tags in the repository!"
    print_info "Available tags:"
    git tag --sort=-version:refname || echo "  None found"
    exit 1
fi

newer_tag=${tags[0]}
older_tag=${tags[1]}

print_info "Newer tag: $newer_tag"
print_info "Older tag: $older_tag"

# Get the commit hashes for the tags
newer_commit=$(git rev-list -n 1 $newer_tag)
older_commit=$(git rev-list -n 1 $older_tag)

print_info "Newer commit: $newer_commit"
print_info "Older commit: $older_commit"

# Create output filename with tag names
output_file="update_${older_tag}_to_${newer_tag}.zip"

print_info "Creating zip file: $output_file"

# Get the list of changed files and filter out files that don't exist at HEAD
all_changed_files=$(git diff --name-only --diff-filter=d $older_commit $newer_commit)

if [ -z "$all_changed_files" ]; then
    print_error "No files changed between $older_tag and $newer_tag"
    exit 1
fi

# Filter files that exist at HEAD
existing_files=""
while IFS= read -r file; do
    if git cat-file -e HEAD:"$file" 2>/dev/null; then
        if [ -z "$existing_files" ]; then
            existing_files="$file"
        else
            existing_files="$existing_files"$'\n'"$file"
        fi
    else
        print_info "Skipping file (doesn't exist at HEAD): $file"
    fi
done <<< "$all_changed_files"

if [ -z "$existing_files" ]; then
    print_error "No existing files to archive between $older_tag and $newer_tag"
    exit 1
fi

print_info "Files to include in zip:"
echo "$existing_files" | sed 's/^/  - /'

# Create the zip archive
git archive --output="$output_file" HEAD $existing_files

print_success "Created $output_file with changes between $older_tag and $newer_tag"
print_info "Archive contains $(echo "$existing_files" | wc -l) files"
