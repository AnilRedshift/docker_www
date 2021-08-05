FROM golang:1.16.6-alpine as builder
RUN set -ex && \
  apk update && \
  apk add --no-cache \
  git

RUN git clone --branch 'v0.3' --single-branch https://github.com/AnilRedshift/captions_please_go /captions_please_go
WORKDIR /captions_please_go/
RUN go mod download
WORKDIR /captions_please_go/cmd/captions_please
RUN go build .

FROM alpine:3.14
COPY --from=builder /captions_please_go/cmd/captions_please/captions_please /captions_please
RUN chmod +x /captions_please

EXPOSE 8080

CMD /captions_please --verbose
