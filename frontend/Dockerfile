# Multi-stage Dockerfile for Angular Frontend
FROM node:20-alpine AS base

WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm ci

# Copy source
COPY . .

# Build stage
FROM base AS builder

RUN npm run build:prod

# Production stage - Nginx
FROM nginx:alpine AS production

# Copy nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy built assets
COPY --from=builder /app/dist/sherpa-frontend/browser /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

# Development stage
FROM base AS development

EXPOSE 4200

CMD ["npm", "run", "start", "--", "--host", "0.0.0.0", "--poll", "2000"]