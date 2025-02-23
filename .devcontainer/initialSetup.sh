#!/bin/bash
if ! [ -e .env ]
then
    cp .env.example .env
fi