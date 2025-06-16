# Automação Papa Leguas

Este documento explica como funciona a automação do sistema Papa Leguas para migração e verificação de padrões.

## 🚀 Instalação Automática

### Durante a Instalação do Pacote

Quando você instala o pacote via Composer, o sistema automaticamente:

1. **Publica configurações**
2. **Publica migrations**
3. **Registra o ServiceProvider**
4. **Oferece migração automática** para padrões Papa Leguas

```bash
composer require callcocam/react-papa-leguas
```

**Durante a instalação você verá:**

```
🚀 React Papa Leguas instalado com sucesso!

Deseja migrar seus modelos e migrations para os padrões Papa Leguas? (yes/no) [yes]:
> yes

 Fazendo backup dos arquivos originais...
 ✅ Backup criado em: storage/app/papa-leguas-backup-2025-06-16-123456

 Atualizando User model...
 ✅ User model atualizado seguindo padrões Papa Leguas

 Gerando migration de atualização...
 ✅ Migration criada: 2025_06_16_123456_update_users_table_papa_leguas_standards.php

✅ Migração concluída! Verifique os arquivos gerados.
📝 Consulte packages/callcocam/react-papa-leguas/UPDATES.md para mais detalhes.

📚 Documentação disponível em:
   - packages/callcocam/react-papa-leguas/DEVELOPMENT_STANDARDS.md
   - packages/callcocam/react-papa-leguas/EXAMPLES.md
   - packages/callcocam/react-papa-leguas/OPTIMIZATION_REPORT.md
```

## 🔍 Verificação de Padrões

### Comando de Verificação

O sistema oferece um comando para verificar se seu projeto está seguindo os padrões:

```bash
php artisan papa-leguas:check-standards
```

**Saída exemplo:**

```
🔍 Verificando padrões Papa Leguas...

📊 Resultado: 4/6 verificações passaram

✅ User Model Exists
✅ Config Files Published  
✅ Shinobi ACL System
❌ User Model Standards
❌ Users Migration Standards
❌ Performance Indexes

⚠️  Seu projeto não está seguindo todos os padrões Papa Leguas.
💡 Execute: php artisan papa-leguas:migrate-standards --backup
```

### Verificação Detalhada

Para ver detalhes específicos do que está faltando:

```bash
php artisan papa-leguas:check-standards --show-details
```

**Saída detalhada:**

```
🔍 Verificando padrões Papa Leguas...

📊 Resultado: 4/6 verificações passaram

✅ User Model Exists
   Todos os padrões implementados
✅ Config Files Published
   3/3 arquivos de configuração publicados
✅ Shinobi ACL System
   Sistema ACL configurado
❌ User Model Standards
   Faltando: ULID, Status Enum, Tenant ID
❌ Users Migration Standards
   Faltando: ULID primary key, Status enum, Performance indexes
❌ Performance Indexes
   Faltando índices em: users(tenant_id), users(status), users(slug)
```

## 🔄 Detecção Automática

### Mensagem no Console

Quando você roda comandos Artisan, o sistema automaticamente verifica se há atualizações disponíveis:

```bash
php artisan migrate

📦 Papa Leguas Standards Update Available
   Seu projeto pode se beneficiar dos padrões Papa Leguas mais recentes.
   Execute: php artisan papa-leguas:migrate-standards --backup
```

### Verificação Silenciosa

O sistema verifica:
- Se o User model tem todos os traits necessários
- Se as migrations seguem os padrões
- Se os índices de performance estão presentes
- Se o sistema Shinobi está configurado

## 🛠️ Migração Manual

### Se Necessário Migrar Manualmente

```bash
# Com backup automático (recomendado)
php artisan papa-leguas:migrate-standards --backup

# Forçar sem confirmação
php artisan papa-leguas:migrate-standards --backup --force

# Ver o que será alterado (dry-run)
php artisan papa-leguas:migrate-standards --dry-run
```

### O que é Migrado

1. **User Model (`app/Models/User.php`)**
   - Adiciona traits: `HasUlids`, `HasSlug`, `SoftDeletes`
   - Adiciona campos: `status`, `tenant_id`, `user_id`
   - Adiciona métodos: `published()`, `draft()`, etc.

2. **Users Migration**
   - Gera nova migration para atualizar a tabela
   - Adiciona colunas necessárias
   - Cria índices de performance
   - Mantém dados existentes

3. **Backup Automático**
   - Cria backup em `storage/app/papa-leguas-backup-TIMESTAMP/`
   - Preserva arquivos originais
   - Permite rollback manual se necessário

## 📋 Checklist de Automação

### ✅ O que é Automatizado

- [x] Detecção de padrões não implementados
- [x] Backup automático de arquivos originais
- [x] Atualização do User model
- [x] Geração de migration incremental
- [x] Configuração do sistema Shinobi
- [x] Publicação de arquivos de configuração
- [x] Verificação pós-instalação

### ⚠️ O que Requer Atenção Manual

- [ ] Customizações específicas do User model
- [ ] Migrations personalizadas existentes
- [ ] Relacionamentos complexos existentes
- [ ] Validações específicas do projeto

## 🔧 Troubleshooting

### Problemas Comuns

**1. Comando não encontrado**
```bash
# Se o comando não for encontrado, rode:
composer dump-autoload
php artisan package:discover
```

**2. Backup não criado**
```bash
# Verifique permissões da pasta storage:
chmod -R 775 storage/app
```

**3. Migration já existe**
```bash
# Se a migration já existe, rode:
php artisan migrate:status
# E depois ajuste manualmente ou use --force
```

**4. Conflito no User model**
```bash
# Se há conflitos, restaure do backup:
cp storage/app/papa-leguas-backup-*/User.php app/Models/User.php
# E faça ajustes manuais
```

### Logs e Debug

O sistema registra todas as operações:

```bash
# Ver logs do Laravel
tail -f storage/logs/laravel.log

# Ver comandos executados
php artisan papa-leguas:check-standards --show-details
```

## 🎯 Fluxo Completo de Automação

1. **Instalação**: `composer require callcocam/react-papa-leguas`
2. **Automação**: Sistema pergunta sobre migração
3. **Backup**: Arquivos originais preservados
4. **Migração**: Models e migrations atualizados
5. **Verificação**: `papa-leguas:check-standards`
6. **Execução**: `php artisan migrate`
7. **Validação**: Sistema funcionando com padrões Papa Leguas

Este fluxo garante que seu projeto seja atualizado de forma segura e automatizada para seguir todos os padrões Papa Leguas, mantendo a compatibilidade e performance.
