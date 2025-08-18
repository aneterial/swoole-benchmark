FROM node:20-alpine AS deps

WORKDIR /app
COPY node/package*.json ./
RUN npm install --only=production --ignore-scripts

FROM node:20-alpine

ARG USER=node

RUN apk add --no-cache dumb-init

WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules

COPY node/ .

RUN chown -R node:node /app

USER node

ENTRYPOINT ["dumb-init", "--"]

CMD ["node", "cluster.js"]
