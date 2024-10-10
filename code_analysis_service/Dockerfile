FROM python:3.9-slim

WORKDIR /app

# Install required packages
RUN pip install google-cloud-aiplatform requests

# Copy the code analyzer script
COPY code_analyzer.py .

# Run the script
CMD ["python", "code_analyzer.py"]
