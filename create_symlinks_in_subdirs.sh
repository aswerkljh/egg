#!/bin/bash

# Function to rename directories by replacing spaces and hyphens with underscores
# Comment out this block if you DO NOT want this behavior.
find_and_replace() {
    for dir in "$1"/*; do
        if [ -d "${dir}" ]; then
            new_dir=$(echo "${dir}" | sed 's/[ -]/_/g')
            if [ "${dir}" != "${new_dir}" ]; then
                mv "${dir}" "${new_dir}"
                echo "Renamed: ${dir} -> ${new_dir}"
            fi
            find_and_replace "${new_dir}"
        fi
    done
}

# Function to traverse subfolders and create symlinks
create_symlink() {
    local dir="$1"
    local source_file="/var/www/drkt.eu/subdomains/p/index.php"

    # Check if the source file exists
    if [ ! -f "${source_file}" ]; then
        echo "Source file not found: ${source_file}"
        exit 1
    fi

    # Traverse the subfolders, excluding ".thumb" directories and the top-level directory
    find "${dir}" -mindepth 1 -type d -not -name ".thumb" | while read -r subdir; do
        # Create the symlink if it doesn't exist already
        if [ ! -L "${subdir}/index.php" ]; then
            ln -s "${source_file}" "${subdir}/index.php"
            echo "Created symlink at: ${subdir}/index.php"
        fi
    done
}

# Start the script execution
script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Call the functions
find_and_replace "${script_dir}"
create_symlink "${script_dir}"

# Fix permissions
#chown -R www-data:www-data /var/www/drkt.eu/subdomains/p
#chmod -R g+rw /var/www/drkt.eu/subdomains/p