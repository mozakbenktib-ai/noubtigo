<?php
foreach(['companies', 'users', 'customers', 'appointments', 'services', 'rooms', 'tickets'] as $table) {
    if (Schema::hasColumn($table, 'uuid')) {
        Schema::dropColumns($table, ['uuid']);
        echo "Dropped from $table\n";
    }
}
echo "Done\n";
