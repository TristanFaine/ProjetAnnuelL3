#!/bin/bash
gnome-terminal -- bash -c "php -S localhost:8000 bash"|| xterm -hold -e php -S localhost8000 || konsole --noclose -e php -S localhost:8000
