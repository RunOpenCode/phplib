#!/usr/bin/env python3

import click
import os
import subprocess
from rich.console import Console

console = Console()
script_directory = os.path.dirname(__file__)
working_directory = os.path.abspath(os.path.join(script_directory, '..'))
os.chdir(working_directory)


@click.command(context_settings=dict(
    ignore_unknown_options=True,
    allow_extra_args=True,
))
@click.argument('args', nargs=-1, type=click.UNPROCESSED)
def composer(args):
    """
    This script will run your composer commands from host environment.
    
    Usage: bin/composer.py [ARGUMENTS] [OPTIONS]
    """
    result = subprocess.run(
        'docker compose -f compose.yaml ps -q php.local',
        stdout=subprocess.PIPE,
        shell=True
    )

    container = result.stdout.decode('utf-8').strip()

    if '' == container:
        console.print(
            'Service  php.local is not running, have you even started it?',
            width=80,
            style="red"
        )
        exit(1)

    os.system(
        'docker exec -w /var/www/html -it {} composer {}'
        .format(container, ' '.join(args))
    )


if __name__ == '__main__':
    composer()
