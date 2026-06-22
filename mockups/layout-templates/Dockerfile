FROM node:22-alpine AS builder

WORKDIR /app
COPY package*.json tailwind.config.js ./
RUN npm ci

COPY home.html admin.html input.css app.js ./
COPY assets ./assets
RUN npm run build \
    && cp home.html admin.html app.js dist/ \
    && cp -R assets dist/assets \
    && cp node_modules/lucide/dist/umd/lucide.min.js dist/lucide.min.js

FROM nginx:1.27-alpine
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /app/dist /usr/share/nginx/html

EXPOSE 80
